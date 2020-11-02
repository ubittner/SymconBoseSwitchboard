<?php

/** @noinspection PhpUnused */
/** @noinspection DuplicatedCode */

/*
 * @module      Bose Switchboard Device
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
 *              Bose Switchboard Device
 *             	{3A8BE899-3400-6755-BCAB-375C47D9451E}
 */

declare(strict_types=1);

include_once __DIR__ . '/../libs/constants.php';
include_once __DIR__ . '/helper/autoload.php';

class BoseSwitchboardDevice extends IPSModule
{
    //Helper
    use BOSESB_deviceControl;

    // Constants
    private const INTERNAL_UPDATE_INTERVAL = 3000;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProperties();
        $this->CreateProfiles();
        $this->RegisterVariables();
        $this->RegisterAttributes();
        //Timer
        $this->RegisterTimer('UpdateDeviceState', 0, 'BOSESB_UpdateDeviceState(' . $this->InstanceID . ');');
        //Connect to splitter
        $this->ConnectParent(BOSE_SWITCHBOARD_SPLITTER_GUID);
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
        $this->DeleteProfiles();
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
        $this->GetDeviceCapabilities();
        $this->UpdateDeviceState();
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
        //Capabilities
        $formData['elements'][6]['items'][0]['value'] = $this->ReadAttributeBoolean('CanAudioNotify');
        $formData['elements'][6]['items'][1]['value'] = $this->ReadAttributeBoolean('CanMute');
        $formData['elements'][6]['items'][2]['value'] = $this->ReadAttributeBoolean('HasPresets');
        $formData['elements'][6]['items'][3]['value'] = $this->ReadAttributeInteger('VolumeMax');
        $formData['elements'][6]['items'][4]['value'] = $this->ReadAttributeInteger('VolumeMin');
        return json_encode($formData);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Power':
                $this->ToggleDevicePower($Value);
                break;

            case 'Mute':
                $this->ToggleDeviceMute($Value);
                break;

            case 'Volume':
                $this->SetDeviceVolume($Value);
                break;

            case 'Presets':
                $this->PlayDevicePreset($Value);
                break;
        }
    }

    //Data from Event Callback URL API, prepared, but not used at the moment!
    public function ReceiveData($JSONString)
    {
        //Received data from parent splitter
        $data = json_decode($JSONString);
        $this->SendDebug(__FUNCTION__, 'Incoming Data: ' . utf8_decode($data->Buffer), 0);
    }

    #################### Private

    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    private function RegisterProperties(): void
    {
        $this->RegisterPropertyString('ProductID', '');
        $this->RegisterPropertyString('ProductType', '');
        $this->RegisterPropertyString('ProductName', '');
        $this->RegisterPropertyInteger('UpdateInterval', 60);
    }

    private function CreateProfiles(): void
    {
        //Volume slider
        $profile = 'BOSESB.' . $this->InstanceID . '.Volume';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileValues($profile, 0, 100, 1);
        IPS_SetVariableProfileText($profile, '', '%');
        IPS_SetVariableProfileIcon($profile, 'Speaker');
        //Presets
        $profile = 'BOSESB.' . $this->InstanceID . '.Presets';
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

    private function DeleteProfiles(): void
    {
        $profiles = ['Volume', 'Presets'];
        foreach ($profiles as $profile) {
            $profileName = 'BOSESB.' . $this->InstanceID . '.' . $profile;
            if (@IPS_VariableProfileExists($profileName)) {
                IPS_DeleteVariableProfile($profileName);
            }
        }
    }

    private function RegisterVariables(): void
    {
        //Power
        $this->RegisterVariableBoolean('Power', $this->Translate('Power'), '~Switch', 10);
        $this->EnableAction('Power');
        //Mute
        $id = @$this->GetIDForIdent('Mute');
        $this->RegisterVariableBoolean('Mute', $this->Translate('Mute'), '~Switch', 20);
        $this->EnableAction('Mute');
        if ($id == false) {
            IPS_SetIcon($this->GetIDForIdent('Mute'), 'Speaker');
        }
        //Volume Slider
        $profile = 'BOSESB.' . $this->InstanceID . '.Volume';
        $this->RegisterVariableInteger('Volume', $this->Translate('Volume'), $profile, 30);
        $this->EnableAction('Volume');
        //Presets
        $profile = 'BOSESB.' . $this->InstanceID . '.Presets';
        $this->RegisterVariableInteger('Presets', $this->Translate('Presets'), $profile, 40);
        $this->EnableAction('Presets');
        //Now Playing
        $id = @$this->GetIDForIdent('NowPlaying');
        $this->RegisterVariableString('NowPlaying', $this->Translate('Now Playing'), '~HTMLBox', 50);
        if ($id == false) {
            IPS_SetIcon($this->GetIDForIdent('NowPlaying'), 'Melody');
        }
    }

    private function RegisterAttributes(): void
    {
        //Capabilities
        $this->RegisterAttributeBoolean('CanAudioNotify', false);
        $this->RegisterAttributeBoolean('CanMute', false);
        $this->RegisterAttributeBoolean('HasPresets', false);
        $this->RegisterAttributeInteger('VolumeMax', 0);
        $this->RegisterAttributeInteger('VolumeMin', 0);
    }

    private function SetUpdateTimer(): void
    {
        $seconds = $this->ReadPropertyInteger('UpdateInterval');
        if ($seconds > 0 && $seconds < 10) {
            //Minimum update interval is 10 seconds
            $seconds = 10;
        }
        $this->SetTimerInterval('UpdateDeviceState', $seconds * 1000);
    }
}