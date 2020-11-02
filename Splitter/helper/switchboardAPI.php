<?php

declare(strict_types=1);

trait BOSESB_switchboardAPI
{
    private $apiVersion = '1.0';
    private $apiKey = 'GAHFdNpCqXbXR005ZLT4Cgb0iUSG4aXA';

    /**
     * Lists the Bose products available to the current user.
     *
     * @return string
     */
    public function ListProducts(): string
    {
        $endpoint = 'https://partners.api.bose.io/products';
        return $this->SendDataToSwitchboard($endpoint, 'GET', '');
    }

    /**
     * Gets information on a single Bose product. Only products returned by the List Products endpoint are accessible for the given user.
     *
     * @param string $ProductID
     * @return string
     */
    public function GetProduct(string $ProductID): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID;
        return $this->SendDataToSwitchboard($endpoint, 'GET', '');
    }

    /**
     * Sends a request to change the speaker mute setting.
     *
     * @param string $ProductID
     * @param string $Mute
     * "ON" "OFF" "TOGGLE"
     *
     * @return string
     */
    public function ChangeMuteSetting(string $ProductID, string $Mute): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/settings/mute';
        $postfields = json_encode(['mute' => $Mute]);
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Sends a request to change the speaker power setting.
     *
     * @param string $ProductID
     * @param string $Power
     * "ON" "STANDBY" "TOGGLE"
     *
     * @return string
     */
    public function ChangePowerSetting(string $ProductID, string $Power): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/settings/power';
        $postfields = json_encode(['power' => $Power]);
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Sends a request to change the speaker volume.
     *
     * @param string $ProductID
     * @param int $Volume
     * @return string
     */
    public function ChangeVolume(string $ProductID, int $Volume): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/settings/volume';
        $postfields = json_encode(['volume' => $Volume]);
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Gets the most recent Now Playing information available for this product.
     *
     * @param string $ProductID
     * @return string
     */
    public function GetNowPlaying(string $ProductID): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content';
        return $this->SendDataToSwitchboard($endpoint, 'GET', '');
    }

    /**
     * Changes the playback state of a product.
     *
     * @param string $ProductID
     * @param string $Command
     * "RESUME" "PAUSE" "RESUME_PAUSE_TOGGLE" "SKIP_NEXT" "SKIP_PREVIOUS"
     *
     * @return string
     */
    public function ControlProduct(string $ProductID, string $Command): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/control';
        $postfields = json_encode(['command' => $Command]);
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Sets the current content source to repeat (if available).
     *
     * @param string $ProductID
     * @param string $Repeat
     * "ONE" "ALL" "OFF"
     *
     * @return string
     */
    public function SetRepeat(string $ProductID, string $Repeat): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/repeat';
        $postfields = json_encode(['repeat' => $Repeat]);
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Sets the current content source to shuffle (if available).
     *
     * @param string $ProductID
     * @param string $Shuffle
     * "ON" "OFF" "TOGGLE"
     *
     * @return string
     */
    public function SetShuffle(string $ProductID, string $Shuffle): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/shuffle';
        $postfields = json_encode(['shuffle' => $Shuffle]);
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Lists the presets available to the current user on this product.
     *
     * @param string $ProductID
     * @return string
     */
    public function ListPresets(string $ProductID): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/presets';
        return $this->SendDataToSwitchboard($endpoint, 'GET', '');
    }

    /**
     * Gets a single preset for the product.
     *
     * @param string $ProductID
     * @param int $PresetID
     * @return string
     */
    public function GetPreset(string $ProductID, int $PresetID): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/presets/' . $PresetID;
        return $this->SendDataToSwitchboard($endpoint, 'GET', '');
    }

    /**
     * Plays a preset on the product.
     *
     * @param string $ProductID
     * @param int $PresetID
     * Identifier for a product preset. A preset identifier is an integer between 1 and 6, inclusive.
     *
     * @return string
     */
    public function PlayPreset(string $ProductID, int $PresetID): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/presets/' . $PresetID . '/play';
        $postfields = '';
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    /**
     * Plays an audio notification.
     *
     * @param string $ProductID
     * @param string $AudioUrl
     * @param int $VolumeOverride
     * @return string
     */
    public function PlayAudioNotification(string $ProductID, string $AudioUrl, int $VolumeOverride = 0): string
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/notify';
        $postfields = json_encode(['url' => $AudioUrl]);
        if ($VolumeOverride != 0) {
            $postfields = json_encode(['url' => $AudioUrl, 'volumeOverride' => $VolumeOverride]);
        }
        return $this->SendDataToSwitchboard($endpoint, 'POST', $postfields);
    }

    #################### Switchboard

    private function SendDataToSwitchboard(string $Endpoint, string $CustomRequest, string $Postfields): string
    {
        $this->SendDebug(__FUNCTION__, 'Endpoint: ' . $Endpoint, 0);
        $this->SendDebug(__FUNCTION__, 'CustomRequest: ' . $CustomRequest, 0);
        $this->SendDebug(__FUNCTION__, 'Postfields: ' . $Postfields, 0);
        $token = $this->FetchAccessToken();
        $timeout = round($this->ReadPropertyInteger('Timeout') / 1000);
        //Send data to endpoint
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST   => $CustomRequest,
            CURLOPT_URL             => $Endpoint,
            CURLOPT_HEADER          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_CONNECTTIMEOUT  => $timeout,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_POSTFIELDS      => $Postfields,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json',
                'X-API-Version: ' . $this->apiVersion,
                'X-ApiKey: ' . $this->apiKey,
                'Authorization: Bearer ' . $token]]);
        $response = curl_exec($ch);
        $body = '';
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: # OK (GET)
                case 202: # Accepted (POST)
                    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $header = substr($response, 0, $header_size);
                    $body = substr($response, $header_size);
                    $this->SendDebug(__FUNCTION__, 'Header: ' . $header, 0);
                    $this->SendDebug(__FUNCTION__, 'Body: ' . $body, 0);
                    break;

                default:
                    $this->SendDebug(__FUNCTION__, 'HTTP Code: ' . $http_code, 0);
            }
        } else {
            $error_msg = curl_error($ch);
            $this->SendDebug(__FUNCTION__, 'An error has occurred: ' . json_encode($error_msg), 0);
        }
        curl_close($ch);
        return $body;
    }
}