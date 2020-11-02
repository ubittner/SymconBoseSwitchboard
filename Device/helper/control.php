<?php

/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait BOSESB_deviceControl
{
    /**
     * Toggles the device off or on.
     *
     * @param bool $State
     * false    = off
     * true     = on
     *
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    public function ToggleDevicePower(bool $State): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Stop update timer
        $this->SetTimerInterval('UpdateDeviceState', 0);
        //Get actual values
        $actualPower = $this->GetValue('Power');
        $this->SetValue('Power', $State);
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ChangePowerSetting';
        switch ($State) {
            case false:
                $power = 'STANDBY';
                break;

            default:
                $power = 'ON';
        }
        $buffer['Params'] = ['productID' => (string) $productID, 'power' => (string) $power];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($result), 0);
        if (!empty($result)) {
            if (is_array($result)) {
                if (array_key_exists('requestID', $result)) {
                    $requestID = $result['requestID'];
                    if (!empty($requestID)) {
                        $success = true;
                    }
                }
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
            //Revert
            $this->SetValue('Power', $actualPower);
            //Reactivate update timer
            $this->SetUpdateTimer();
        }
        //Activate update timer after three seconds, so we can expect that the data is updated on switchboard
        $this->SetTimerInterval('UpdateDeviceState', self::INTERNAL_UPDATE_INTERVAL);
        return $success;
    }

    /**
     * Toggles the device mute off or on.
     *
     * @param bool $State
     * false    = unmute
     * true     = mute
     *
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    public function ToggleDeviceMute(bool $State): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Stop update timer
        $this->SetTimerInterval('UpdateDeviceState', 0);
        //Get actual values
        $actualState = $this->GetValue('Mute');
        $this->SetValue('Mute', $State);
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ChangeMuteSetting';
        switch ($State) {
            case false:
                $mute = 'OFF';
                break;

            default:
                $mute = 'ON';
        }
        $buffer['Params'] = ['productID' => (string) $productID, 'mute' => (string) $mute];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($result), 0);
        if (!empty($result)) {
            if (is_array($result)) {
                if (array_key_exists('requestID', $result)) {
                    $requestID = $result['requestID'];
                    if (!empty($requestID)) {
                        $success = true;
                    }
                }
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
            //Revert
            $this->SetValue('Mute', $actualState);
            //Reactivate update timer
            $this->SetUpdateTimer();
        }
        //Activate update timer after three seconds, so we can expect that the data is updated on switchboard
        $this->SetTimerInterval('UpdateDeviceState', self::INTERNAL_UPDATE_INTERVAL);
        return $success;
    }

    /**
     * Changes the device volume.
     *
     * @param int $Volume
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    public function SetDeviceVolume(int $Volume): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Stop update timer
        $this->SetTimerInterval('UpdateDeviceState', 0);
        //Get actual values
        $actualVolume = $this->GetValue('Volume');
        $this->SetValue('Volume', $Volume);
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ChangeVolume';
        $buffer['Params'] = ['productID' => (string) $productID, 'volume' => (int) $Volume];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($result), 0);
        if (!empty($result)) {
            if (is_array($result)) {
                if (array_key_exists('requestID', $result)) {
                    $requestID = $result['requestID'];
                    if (!empty($requestID)) {
                        $success = true;
                    }
                }
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
            //Revert
            $this->SetValue('Volume', $actualVolume);
            //Reactivate update timer
            $this->SetUpdateTimer();
        }
        //Activate update timer after three seconds, so we can expect that the data is updated on switchboard
        $this->SetTimerInterval('UpdateDeviceState', self::INTERNAL_UPDATE_INTERVAL);
        return $success;
    }

    /**
     * Plays a device preset 1 - 6.
     *
     * @param int $Preset
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    public function PlayDevicePreset(int $Preset): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Stop update timer
        $this->SetTimerInterval('UpdateDeviceState', 0);
        //Get actual values
        $actualPreset = $this->GetValue('Presets');
        $this->SetValue('Presets', $Preset);
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'PlayPreset';
        $buffer['Params'] = ['productID' => (string) $productID, 'preset' => (int) $Preset];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($result), 0);
        if (!empty($result)) {
            if (is_array($result)) {
                if (array_key_exists('requestID', $result)) {
                    $requestID = $result['requestID'];
                    if (!empty($requestID)) {
                        $success = true;
                    }
                }
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
            //Revert
            $this->SetValue('Presets', $actualPreset);
            //Reactivate update timer
            $this->SetUpdateTimer();
        }
        //Activate update timer after three seconds, so we can expect that the data is updated on switchboard
        $this->SetTimerInterval('UpdateDeviceState', self::INTERNAL_UPDATE_INTERVAL);
        return $success;
    }

    /**
     * Updates the device state.
     */
    public function UpdateDeviceState(): void
    {
        $this->UpdatePowerMuteVolume();
        $this->UpdatePresets();
        $this->UpdateNowPlaying();
        $this->SetUpdateTimer();
    }

    /**
     * Gets the device capabilities and writes it to the attributes.
     *
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    public function GetDeviceCapabilities(): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetProduct';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $response = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($response), 0);
        if (!empty($response)) {
            if (array_key_exists('capabilities', $response)) {
                $success = true;
                $capabilities = $response['capabilities'];
                if (array_key_exists('canAudioNotify', $capabilities)) {
                    $this->WriteAttributeBoolean('CanAudioNotify', boolval($capabilities['canAudioNotify']));
                }
                if (array_key_exists('canMute', $capabilities)) {
                    $this->WriteAttributeBoolean('CanMute', boolval($capabilities['canMute']));
                }
                if (array_key_exists('hasPresets', $capabilities)) {
                    $this->WriteAttributeBoolean('HasPresets', boolval($capabilities['hasPresets']));
                }
                if (array_key_exists('volumeMax', $capabilities)) {
                    $this->WriteAttributeInteger('VolumeMax', intval($capabilities['volumeMax']));
                }
                if (array_key_exists('volumeMin', $capabilities)) {
                    $this->WriteAttributeInteger('VolumeMin', intval($capabilities['volumeMin']));
                }
                //Set volume slider profile
                $profile = 'BOSESB.' . $this->InstanceID . '.Volume';
                $volumeMax = $this->ReadAttributeInteger('VolumeMax');
                $volumeMin = $this->ReadAttributeInteger('VolumeMin');
                IPS_SetVariableProfileValues($profile, $volumeMin, $volumeMax, 1);
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
        }
        return $success;
    }

    #################### Private

    /**
     * Checks if the parent splitter is active.
     *
     * @return bool
     * false    = inactive
     * true     = active
     */
    private function CheckParentSplitter(): bool
    {
        $result = true;
        if (!$this->HasActiveParent()) {
            $this->SendDebug(__FUNCTION__, 'Parent splitter is inactive!', 0);
            $result = false;
        }
        return $result;
    }

    /**
     * Gets the product id.
     *
     * @return string
     */
    private function GetProductID(): string
    {
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            $this->SendDebug(__FUNCTION__, 'Missing or empty product id!', 0);
        }
        return $productID;
    }

    /**
     * Updates the power, mute and volume variables.
     *
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    private function UpdatePowerMuteVolume(): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetProduct';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($result), 0);
        if (!empty($result)) {
            if (array_key_exists('settings', $result)) {
                $success = true;
                // Power
                if (array_key_exists('power', $result['settings'])) {
                    if (!empty($result['settings']['power'])) {
                        $power = true;
                        if ((string) $result['settings']['power'] == 'STANDBY') {
                            $power = false;
                        }
                        $this->SetValue('Power', $power);
                    }
                }
                // Mute
                if (array_key_exists('mute', $result['settings'])) {
                    $success = true;
                    if (!empty($result['settings']['mute'])) {
                        $mute = true;
                        if ((string) $result['settings']['mute'] == 'OFF') {
                            $mute = false;
                        }
                        $this->SetValue('Mute', $mute);
                    }
                }
                // Volume
                if (array_key_exists('volume', $result['settings'])) {
                    $success = true;
                    if (!empty($result['settings']['volume'])) {
                        $volume = (int) $result['settings']['volume'];
                        $this->SetValue('Volume', $volume);
                    }
                }
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
        }
        return $success;
    }

    /**
     * Updates the presets variable profile.
     *
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    private function UpdatePresets(): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $hasPresets = $this->ReadAttributeBoolean('HasPresets');
        if (!$hasPresets) {
            $this->SendDebug(__FUNCTION__, 'Device has no presets!', 0);
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ListPresets';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $presets = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($presets), 0);
        if (!empty($presets)) {
            $success = true;
            $profile = 'BOSESB.' . $this->InstanceID . '.Presets';
            foreach ($presets as $key => $preset) {
                if (array_key_exists('presetID', $preset)) {
                    $presetID = $preset['presetID'];
                    if (array_key_exists('name', $preset)) {
                        IPS_SetVariableProfileAssociation($profile, $presetID, $preset['name'], '', 0x0000FF);
                    }
                }
            }
        }
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
        }
        return $success;
    }

    /**
     * Updates the now playing variable.
     *
     * @return bool
     * false    = an error occurred
     * true     = ok
     */
    private function UpdateNowPlaying(): bool
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $success = false;
        if (!$this->CheckParentSplitter()) {
            return $success;
        }
        $productID = $this->GetProductID();
        if (empty($productID)) {
            return $success;
        }
        //Send data to switchboard splitter
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetNowPlaying';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $content = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, 'Result: ' . json_encode($content), 0);
        $sourceDisplayName = '';
        $trackArt = 'https://raw.githubusercontent.com/ubittner/SymconBoseSwitchboard/master/imgs/bose_logo_blackbox.png';
        $artist = '';
        $trackName = '';
        $album = '';
        if (!empty($content)) {
            if (array_key_exists('source', $content)) {
                $success = true;
                //Source display name
                if (array_key_exists('sourceDisplayName', $content['source'])) {
                    if ((string) $content['source']['sourceDisplayName'] != 'INVALID_SOURCE') {
                        $sourceDisplayName = (string) $content['source']['sourceDisplayName'];
                    }
                }
            }
            if (array_key_exists('metadata', $content)) {
                $success = true;
                //Track art
                if (array_key_exists('trackArt', $content['metadata'])) {
                    if (!empty($content['metadata']['trackArt'])) {
                        $trackArt = (string) $content['metadata']['trackArt'];
                    }
                }
                //Artist
                if (array_key_exists('artist', $content['metadata'])) {
                    $success = true;
                    $artist = (string) $content['metadata']['artist'];
                    //Set preset button
                    $hasPresets = $this->ReadAttributeBoolean('HasPresets');
                    if ($hasPresets) {
                        $profile = 'BOSESB.' . $this->InstanceID . '.Presets';
                        $associations = IPS_GetVariableProfile($profile)['Associations'];
                        if (!empty($associations)) {
                            foreach ($associations as $association) {
                                if ($association['Name'] == $artist) {
                                    $this->SetValue('Presets', $association['Value']);
                                }
                            }
                        }
                    }
                }
                //Track name
                if (array_key_exists('trackName', $content['metadata'])) {
                    $success = true;
                    $trackName = (string) $content['metadata']['trackName'];
                    //Set preset button
                    $hasPresets = $this->ReadAttributeBoolean('HasPresets');
                    if ($hasPresets) {
                        $profile = 'BOSESB.' . $this->InstanceID . '.Presets';
                        $associations = IPS_GetVariableProfile($profile)['Associations'];
                        if (!empty($associations)) {
                            foreach ($associations as $association) {
                                if ($association['Name'] == $trackName) {
                                    $this->SetValue('Presets', $association['Value']);
                                }
                            }
                        }
                    }
                }
                //Album
                if (array_key_exists('album', $content['metadata'])) {
                    $success = true;
                    $album = (string) $content['metadata']['album'];
                }
            }
        }
        $nowPlaying = '<!doctype html>
            <html lang="de">
            <head>
            <meta charset="utf-8">
            <title>Media Info</title>
            <style type="text/css">
            .cover {
                display: block;
                float: left;
                padding: 8px;
            }
            .mediainfo .cover {
                -webkit-box-shadow: 2px 2px 5px hsla(0,0%,0%,1.00);
               box-shadow: 2px 2px 5px hsla(0,0%,0%,1.00);
            }
            .description {
                vertical-align: bottom;
                float: none;
                padding-top: 33px;
                padding-right: 21px;
                padding-bottom: 21px;
                margin-top: 0;
                margin-left: 170px;
            }
            .source {
                font-size: initial;
            }
            .artist {
                font-size: initial;
            }
            .track {
                font-size: initial;
            }
            .album {
                font-size: initial;
            }
            </style>
            </head>
            <body>
            <main class="mediainfo">
              <section class="cover"><img src="' . $trackArt . '" width="145" height="145" id="cover" alt="cover"></section>
              <section class="description">
                <div class="source">' . $sourceDisplayName . '</div>
                <div class="artist">' . $artist . '</div>
                <div class="track">' . $trackName . '</div>
                <div class="album">' . $album . '</div>
              </section>
            </main>
            </body>
            </html>';
        $this->SetValue('NowPlaying', $nowPlaying);
        if (!$success) {
            $this->SendDebug(__FUNCTION__, 'Method failed!', 0);
        }
        return $success;
    }
}