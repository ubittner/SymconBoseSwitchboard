<?php /** @noinspection DuplicatedCode */

/*
 * @module      Bose Switchboard Splitter
 *
 * @prefix      BSBS
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2020
 * @license     CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @see         https://github.com/ubittner/SymconBoseSwitchboard/Splitter
 *
 * @guids       Library
 *              {7101C0B6-7A8D-1FE2-A427-8DDCA26C3244}
 *
 *              Bose Switchboard Splitter
 *             	{B6558215-A729-3DCC-909F-DC00A0E2A734}
 */

declare(strict_types=1);

// Include
include_once __DIR__ . '/../libs/helper/autoload.php';
include_once __DIR__ . '/helper/autoload.php';

class BoseSwitchboardSplitter extends IPSModule
{
    // Helper
    use libs_helper_getModuleInfo;
    use BSBS_switchboardAPI;
    use BSBS_webHook;
    use BSBS_webOAuth;

    public function Create()
    {
        // Never delete this line!
        parent::Create();
        // Properties
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyInteger('Timeout', 5000);
        $this->RegisterPropertyString('Token', '');
    }

    public function Destroy()
    {
        // Unregister WebHook
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterWebhook('/hook/' . $this->oauthIdentifer);
        }
        // Unregister WebOAuth
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterWebOAuth($this->oauthIdentifer);
        }
        // Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        // Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        // Never delete this line!
        parent::ApplyChanges();
        // Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterWebHook('/hook/' . $this->oauthIdentifer);
        $this->RegisterWebOAuth($this->oauthIdentifer);
        $this->ValidateConfiguration();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        if (!empty($Data)) {
            foreach ($Data as $key => $value) {
                $this->SendDebug(__FUNCTION__, 'Data[' . $key . '] = ' . json_encode($value), 0);
            }
        }
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

        }
    }

    public function GetConfigurationForm()
    {
        $formData = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $moduleInfo = $this->GetModuleInfo(BOSE_SWITCHBOARD_SPLITTER_GUID);
        $formData['elements'][1]['items'][1]['caption'] = $this->Translate("Instance ID:\t\t") . $this->InstanceID;
        $formData['elements'][1]['items'][2]['caption'] = $this->Translate("Module:\t\t\t") . $moduleInfo['name'];
        $formData['elements'][1]['items'][3]['caption'] = "Version:\t\t\t" . $moduleInfo['version'];
        $formData['elements'][1]['items'][4]['caption'] = $this->Translate("Date:\t\t\t") . $moduleInfo['date'];
        $formData['elements'][1]['items'][5]['caption'] = $this->Translate("Time:\t\t\t") . $moduleInfo['time'];
        $formData['elements'][1]['items'][6]['caption'] = $this->Translate("Developer:\t\t") . $moduleInfo['developer'];
        $formData['elements'][1]['items'][7]['caption'] = "API Version:\t\t" . $this->apiVersion;
        return json_encode($formData);
    }

    public function ForwardData($JSONString)
    {
        $this->SendDebug(__FUNCTION__, $JSONString, 0);
        $data = json_decode($JSONString);
        switch ($data->Buffer->Command) {
            case 'ListProducts':
                $response = $this->ListProducts();
                break;

            case 'GetProduct':
                $params = (array) $data->Buffer->Params;
                $response = $this->GetProduct($params['productID']);
                break;

            case 'ChangeMuteSetting':
                $params = (array) $data->Buffer->Params;
                $response = $this->ChangeMuteSetting($params['productID'], $params['mute']);
                break;

            case 'ChangePowerSetting':
                $params = (array) $data->Buffer->Params;
                $response = $this->ChangePowerSetting($params['productID'], $params['power']);
                break;

            case 'ChangeVolume':
                $params = (array) $data->Buffer->Params;
                $response = $this->ChangeVolume($params['productID'], $params['volume']);
                break;

            case 'GetNowPlaying':
                $params = (array) $data->Buffer->Params;
                $response = $this->GetNowPlaying($params['productID']);
                break;

            case 'ControlProduct':
                $params = (array) $data->Buffer->Params;
                $response = $this->ControlProduct($params['productID'], $params['command']);
                break;

            case 'SetRepeat':
                $params = (array) $data->Buffer->Params;
                $response = $this->SetRepeat($params['productID'], $params['repeat']);
                break;

            case 'SetShuffle':
                $params = (array) $data->Buffer->Params;
                $response = $this->SetShuffle($params['productID'], $params['shuffle']);
                break;

            case 'ListPresets':
                $params = (array) $data->Buffer->Params;
                $response = $this->ListPresets($params['productID']);
                break;

            case 'PlayPreset':
                $params = (array) $data->Buffer->Params;
                $response = $this->PlayPreset($params['productID'], $params['preset']);
                break;

            case 'PlayAudioNotification':
                $params = (array) $data->Buffer->Params;
                $response = $this->PlayAudioNotification($params['productID'], $params['audioUrl'], $params['volumeOverride']);
                break;

            default:
                $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
                $response = '';
        }
        $this->SendDebug(__FUNCTION__, $response, 0);
        return $response;
    }

    //#################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function ValidateConfiguration()
    {
        $status = 102;
        // Check token
        $token = $this->ReadPropertyString('Token');
        if (empty($token)) {
            $status = 201;
        }
        $this->SetStatus($status);
    }
}