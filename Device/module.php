<?php

/*
 * @module      Bose Switchboard Device
 *
 * @prefix      BSBD
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2020
 * @license     CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @see         https://github.com/ubittner/SymconBoseSwitchboard/Device
 *
 * @guids       Library
 *              {7101C0B6-7A8D-1FE2-A427-8DDCA26C3244}
 *
 *              Bose Switchboard Device
 *             	{3A8BE899-3400-6755-BCAB-375C47D9451E}
 */

declare(strict_types=1);

// Include
include_once __DIR__ . '/../libs/helper/autoload.php';
include_once __DIR__ . '/helper/autoload.php';

class BoseSwitchboardDevice extends IPSModule
{
    // Constants
    private const DELAY_MILLISECONDS = 250;

    // Helper
    use BSBD_control;

    public function Create()
    {
        // Never delete this line!
        parent::Create();
        $this->RegisterProperties();
        $this->CreateProfiles();
        $this->RegisterVariables();
        $this->RegisterAttributes();
        $this->RegisterUpdateTimer();
        // Connect to parent (Splitter)
        $this->ConnectParent(BOSE_SWITCHBOARD_SPLITTER_GUID);
    }

    public function Destroy()
    {
        // Never delete this line!
        parent::Destroy();
        $this->DeleteProfiles();
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
        $this->SetUpdateTimerInterval();
        $this->UpdateState();
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
        $moduleInfo = [];
        $library = IPS_GetLibrary(BOSE_SWITCHBOARD_LIBRARY_GUID);
        $module = IPS_GetModule(BOSE_SWITCHBOARD_DEVICE_GUID);
        $moduleInfo['name'] = $module['ModuleName'];
        $moduleInfo['version'] = $library['Version'] . '-' . $library['Build'];
        $moduleInfo['date'] = date('d.m.Y', $library['Date']);
        $moduleInfo['time'] = date('H:i', $library['Date']);
        $moduleInfo['developer'] = $library['Author'];
        $formData['elements'][1]['items'][1]['caption'] = $this->Translate("Instance ID:\t\t") . $this->InstanceID;
        $formData['elements'][1]['items'][2]['caption'] = $this->Translate("Module:\t\t\t") . $moduleInfo['name'];
        $formData['elements'][1]['items'][3]['caption'] = "Version:\t\t\t" . $moduleInfo['version'];
        $formData['elements'][1]['items'][4]['caption'] = $this->Translate("Date:\t\t\t") . $moduleInfo['date'];
        $formData['elements'][1]['items'][5]['caption'] = $this->Translate("Time:\t\t\t") . $moduleInfo['time'];
        $formData['elements'][1]['items'][6]['caption'] = $this->Translate("Developer:\t\t") . $moduleInfo['developer'];
        $formData['elements'][2]['items'][0]['value'] = $this->ReadAttributeBoolean('CanAudioNotify');
        $formData['elements'][2]['items'][1]['value'] = $this->ReadAttributeBoolean('CanMute');
        $formData['elements'][2]['items'][2]['value'] = $this->ReadAttributeBoolean('HasPresets');
        $formData['elements'][2]['items'][3]['value'] = $this->ReadAttributeInteger('VolumeMax');
        $formData['elements'][2]['items'][4]['value'] = $this->ReadAttributeInteger('VolumeMin');
        return json_encode($formData);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Power':
                $this->TogglePower($Value);
                break;

            case 'Mute':
                $this->ToggleMute($Value);
                break;

            case 'Volume':
                $this->ChangeVolume($Value);
                break;

            case 'Presets':
                $this->PlayPreset($Value);
                break;
        }
    }

    // Data from Event Callback URL API, prepared, but not used at the moment!
    public function ReceiveData($JSONString)
    {
        // Received data from splitter
        $data = json_decode($JSONString);
        $this->SendDebug(__FUNCTION__, utf8_decode($data->Buffer), 0);
    }

    public function TriggerUpdateState()
    {
        $this->UpdateState();
        $this->SetUpdateTimerInterval();
    }

    //#################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function DeleteProfiles(): void
    {
        $profiles = ['Volume', 'Presets'];
        foreach ($profiles as $profile) {
            $profileName = 'BSBD.' . $this->InstanceID . '.' . $profile;
            if (@IPS_VariableProfileExists($profileName)) {
                IPS_DeleteVariableProfile($profileName);
            }
        }
    }

    private function RegisterProperties()
    {
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyString('ProductID', '');
        $this->RegisterPropertyString('ProductType', '');
        $this->RegisterPropertyString('ProductName', '');
        $this->RegisterPropertyInteger('UpdateInterval', 60);
    }

    private function CreateProfiles()
    {
        // Volume slider
        $profile = 'BSBD.' . $this->InstanceID . '.Volume';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileValues($profile, 0, 100, 1);
        IPS_SetVariableProfileText($profile, '', '%');
        IPS_SetVariableProfileIcon($profile, 'Speaker');
        // Presets
        $profile = 'BSBD.' . $this->InstanceID . '.Presets';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Menu');
        IPS_SetVariableProfileAssociation($profile, 1, 'Preset 1', '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 2, 'Preset 2', '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 3, 'Preset 3', '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 4, 'Preset 4', '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 5, 'Preset 5', '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 6, 'Preset 6', '', 0x0000FF);
    }

    private function RegisterVariables()
    {
        // Power
        $this->RegisterVariableBoolean('Power', 'Power', '~Switch', 10);
        $this->EnableAction('Power');
        // Mute
        $this->RegisterVariableBoolean('Mute', 'Mute', '~Switch', 20);
        $this->EnableAction('Mute');
        IPS_SetIcon($this->GetIDForIdent('Mute'), 'Speaker');
        // Volume Slider
        $profile = 'BSBD.' . $this->InstanceID . '.Volume';
        $this->RegisterVariableInteger('Volume', 'Volume', $profile, 30);
        $this->EnableAction('Volume');
        // Presets
        $profile = 'BSBD.' . $this->InstanceID . '.Presets';
        $this->RegisterVariableInteger('Presets', 'Presets', $profile, 40);
        $this->EnableAction('Presets');
        // Now Playing
        $this->RegisterVariableString('NowPlaying', 'Now Playing', '~HTMLBox', 50);
        IPS_SetIcon($this->GetIDForIdent('NowPlaying'), 'Melody');
    }

    private function RegisterAttributes()
    {
        $this->RegisterAttributeBoolean('CanAudioNotify', false);
        $this->RegisterAttributeBoolean('CanMute', false);
        $this->RegisterAttributeBoolean('HasPresets', false);
        $this->RegisterAttributeInteger('VolumeMax', 0);
        $this->RegisterAttributeInteger('VolumeMin', 0);
    }

    private function RegisterUpdateTimer()
    {
        $this->RegisterTimer('UpdateState', 0, 'BSBD_TriggerUpdateState(' . $this->InstanceID . ');');
    }

    private function SetUpdateTimerInterval()
    {
        $seconds = $this->ReadPropertyInteger('UpdateInterval');
        if ($seconds > 0 && $seconds < 10) {
            // Minimum update interval is 10 seconds
            $seconds = 10;
        }
        $this->SetTimerInterval('UpdateState', $seconds * 1000);
    }
}