<?php /** @noinspection ALL */
/** @noinspection PhpUnused */

declare(strict_types=1);

trait BSBD_control
{
    public function TogglePower(bool $State)
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $this->SetValue('Power', $State);
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
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
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        IPS_Sleep(self::DELAY_MILLISECONDS);
        $this->UpdateState();
    }

    public function ToggleMute(bool $State)
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $this->SetValue('Mute', $State);
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
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
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
    }

    public function ChangeVolume(int $Volume)
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ChangeVolume';
        $buffer['Params'] = ['productID' => (string) $productID, 'volume' => (int) $Volume];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
    }

    public function PlayPreset(int $Preset)
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $this->SetValue('Presets', $Preset);
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'PlayPreset';
        $buffer['Params'] = ['productID' => (string) $productID, 'preset' => (int) $Preset];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        IPS_Sleep(self::DELAY_MILLISECONDS);
        $this->UpdateState();
    }

    public function UpdateState()
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetProduct';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $result = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, json_encode($result), 0);
        if (!empty($result)) {
            if (array_key_exists('settings', $result)) {
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
                    if (!empty($result['settings']['volume'])) {
                        $volume = (int) $result['settings']['volume'];
                        $this->SetValue('Volume', $volume);
                    }
                }
            }
        }
        IPS_Sleep(self::DELAY_MILLISECONDS);
        $this->UpdateNowPlaying();
        IPS_Sleep(self::DELAY_MILLISECONDS);
        $this->GetCapabilities();
        IPS_Sleep(self::DELAY_MILLISECONDS);
        $this->RenamePresets();
    }

    //#################### Private

    private function GetCapabilities()
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetProduct';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $response = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, json_encode($response), 0);
        if (!empty($response)) {
            if (array_key_exists('capabilities', $response)) {
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
                // Set volume slider profile
                $profile = 'BSBD.' . $this->InstanceID . '.Volume';
                $volumeMax = $this->ReadAttributeInteger('VolumeMax');
                $volumeMin = $this->ReadAttributeInteger('VolumeMin');
                IPS_SetVariableProfileValues($profile, $volumeMin, $volumeMax, 1);
            }
        }
    }

    private function RenamePresets()
    {
        $hasPresets = $this->ReadAttributeBoolean('HasPresets');
        if (!$hasPresets) {
            return;
        }
        if (!$this->HasActiveParent()) {
            return;
        }
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ListPresets';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $presets = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, json_encode($presets), 0);
        if (!empty($presets)) {
            $profile = 'BSBD.' . $this->InstanceID . '.Presets';
            foreach ($presets as $key => $preset) {
                if (array_key_exists('presetID', $preset)) {
                    $presetID = $preset['presetID'];
                    if (array_key_exists('name', $preset)) {
                        IPS_SetVariableProfileAssociation($profile, $presetID, $preset['name'], '', 0x0000FF);
                    }
                }
            }
        }
    }

    private function UpdateNowPlaying()
    {
        if (!$this->HasActiveParent()) {
            return;
        }
        $data = [];
        $buffer = [];
        $productID = $this->ReadPropertyString('ProductID');
        if (empty($productID)) {
            return;
        }
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'GetNowPlaying';
        $buffer['Params'] = ['productID' => (string) $productID];
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        $content = json_decode($this->SendDataToParent($data), true);
        $this->SendDebug(__FUNCTION__, json_encode($content), 0);
        $sourceDisplayName = '';
        $trackArt = 'https://raw.githubusercontent.com/ubittner/SymconBoseSwitchboard/master/imgs/bose_logo_blackbox.png';
        $artist = '';
        $trackName = '';
        $album = '';
        if (!empty($content)) {
            if (array_key_exists('source', $content)) {
                // Source display name
                if (array_key_exists('sourceDisplayName', $content['source'])) {
                    if ((string) $content['source']['sourceDisplayName'] != 'INVALID_SOURCE') {
                        $sourceDisplayName = (string) $content['source']['sourceDisplayName'];
                    }
                }
            }
            if (array_key_exists('metadata', $content)) {
                // Track art
                if (array_key_exists('trackArt', $content['metadata'])) {
                    if (!empty($content['metadata']['trackArt'])) {
                        $trackArt = (string) $content['metadata']['trackArt'];
                    }
                }
                // Artist
                if (array_key_exists('artist', $content['metadata'])) {
                    $artist = (string) $content['metadata']['artist'];
                }
                // Track name
                if (array_key_exists('trackName', $content['metadata'])) {
                    $trackName = (string) $content['metadata']['trackName'];
                    // Set preset button
                    $hasPresets = $this->ReadAttributeBoolean('HasPresets');
                    if ($hasPresets) {
                        $profile = 'BSBD.' . $this->InstanceID . '.Presets';
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
                // Album
                if (array_key_exists('album', $content['metadata'])) {
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
    }
}