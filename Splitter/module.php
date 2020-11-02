<?php

/** @noinspection PhpUnused */

/*
 * @module      Bose Switchboard Splitter
 *
 * @prefix      BOSESB
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2020
 * @license     CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @see         https://github.com/ubittner/SymconBoseSwitchboard/
 *
 * @guids       Library
 *              {7101C0B6-7A8D-1FE2-A427-8DDCA26C3244}
 *
 *              Bose Switchboard Splitter
 *             	{B6558215-A729-3DCC-909F-DC00A0E2A734}
 */

declare(strict_types=1);

include_once __DIR__ . '/../libs/constants.php';
include_once __DIR__ . '/helper/autoload.php';

class BoseSwitchboardSplitter extends IPSModule
{
    //Helper
    use BOSESB_switchboardAPI;
    use BOSESB_webHook;
    use BOSESB_webOAuth;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        //Register properties
        $this->RegisterProperties();
    }

    public function Destroy()
    {
        //Unregister WebHook
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterWebhook('/hook/' . $this->oauthIdentifier);
        }
        //Unregister WebOAuth
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterWebOAuth($this->oauthIdentifier);
        }
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        //Never delete this line!
        parent::ApplyChanges();
        //Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterWebHook('/hook/' . $this->oauthIdentifier);
        $this->RegisterWebOAuth($this->oauthIdentifier);
        $this->ValidateConfiguration();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug('MessageSink', 'SenderID: ' . $SenderID . ', Message: ' . $Message, 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

        }
    }

    public function GetConfigurationForm()
    {
        $formData = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
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

            case 'GetPreset':
                $params = (array) $data->Buffer->Params;
                $response = $this->GetPreset($params['productID'], $params['presetID']);
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

    public function LogTokens(): void
    {
        //Refresh token
        $refreshToken = $this->ReadPropertyString('RefreshToken');
        if (!empty($refreshToken)) {
            $this->LogMessage('Refresh Token: ' . $refreshToken, KL_NOTIFY);
        } else {
            $this->LogMessage('No refresh token found!', KL_NOTIFY);
        }
        //Access token
        $data = $this->GetBuffer('AccessToken');
        if ($data != '') {
            $data = json_decode($data);
            if (time() < $data->Expires) {
                $this->LogMessage('Access Token:  ' . $data->Token, KL_NOTIFY);
                $this->LogMessage('Access Token Expires: ' . date('d.m.y H:i:s', $data->Expires), KL_NOTIFY);
            }
        } else {
            $this->LogMessage('No access token found!', KL_NOTIFY);
        }
    }

    #################### Private

    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    private function RegisterProperties(): void
    {
        $this->RegisterPropertyBoolean('Active', false);
        $this->RegisterPropertyInteger('Timeout', 5000);
        $this->RegisterPropertyString('RefreshToken', '');
    }

    private function ValidateConfiguration(): void
    {
        $status = 102;
        //Check refresh token
        $token = $this->ReadPropertyString('RefreshToken');
        if (empty($token)) {
            $status = 201;
        }
        //Check instance
        $active = $this->CheckInstance();
        if (!$active) {
            $status = 104;
        }
        $this->SetStatus($status);
    }

    private function CheckInstance(): bool
    {
        $result = $this->ReadPropertyBoolean('Active');
        if (!$result) {
            $this->SendDebug(__FUNCTION__, 'Instance is inactive!', 0);
        }
        return $result;
    }
}