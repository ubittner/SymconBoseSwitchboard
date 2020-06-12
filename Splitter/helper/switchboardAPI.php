<?php

declare(strict_types=1);

trait BSBS_switchboardAPI
{
    private $apiVersion = '0.9';
    private $apiKey = 'GAHFdNpCqXbXR005ZLT4Cgb0iUSG4aXA';

    /**
     * Lists the Bose products available to the current user.
     *
     * @return false|string
     */
    public function ListProducts()
    {
        $endpoint = 'https://partners.api.bose.io/products';
        return $this->GetSwitchboardData($endpoint);
    }

    /**
     * Gets information on a single Bose product. Only products returned by the List Products endpoint are accessible for the given user.
     *
     * @param string $ProductID
     * @return false|string
     */
    public function GetProduct(string $ProductID)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID;
        return $this->GetSwitchboardData($endpoint);
    }

    /**
     * Sends a request to change the speaker mute setting.
     *
     * @param string $ProductID
     * @param string $Mute
     * "ON" "OFF" "TOGGLE"
     *
     * @return false|string
     */
    public function ChangeMuteSetting(string $ProductID, string $Mute)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/settings/mute';
        $postfields = json_encode(['mute' => $Mute]);
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Sends a request to change the speaker power setting.
     *
     * @param string $ProductID
     * @param string $Power
     * "ON" "STANDBY" "TOGGLE"
     *
     * @return false|string
     */
    public function ChangePowerSetting(string $ProductID, string $Power)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/settings/power';
        $postfields = json_encode(['power' => $Power]);
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Sends a request to change the speaker volume.
     *
     * @param string $ProductID
     * @param int $Volume
     * @return false|string
     */
    public function ChangeVolume(string $ProductID, int $Volume)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/settings/volume';
        $postfields = json_encode(['volume' => $Volume]);
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Gets the most recent Now Playing information available for this product.
     *
     * @param string $ProductID
     * @return false|string
     */
    public function GetNowPlaying(string $ProductID)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content';
        return $this->GetSwitchboardData($endpoint);
    }

    /**
     * Changes the playback state of a product.
     *
     * @param string $ProductID
     * @param string $Command
     * "RESUME" "PAUSE" "RESUME_PAUSE_TOGGLE" "SKIP_NEXT" "SKIP_PREVIOUS"
     *
     * @return false|string
     */
    public function ControlProduct(string $ProductID, string $Command)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/control';
        $postfields = json_encode(['command' => $Command]);
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Sets the current content source to repeat (if available).
     *
     * @param string $ProductID
     * @param string $Repeat
     * "ONE" "ALL" "OFF"
     *
     * @return false|string
     */
    public function SetRepeat(string $ProductID, string $Repeat)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/repeat';
        $postfields = json_encode(['repeat' => $Repeat]);
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Sets the current content source to shuffle (if available).
     *
     * @param string $ProductID
     * @param string $Shuffle
     * "ON" "OFF" "TOGGLE"
     *
     * @return false|string
     */
    public function SetShuffle(string $ProductID, string $Shuffle)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/shuffle';
        $postfields = json_encode(['shuffle' => $Shuffle]);
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Lists the presets available to the current user on this product.
     *
     * @param string $ProductID
     * @return false|string
     */
    public function ListPresets(string $ProductID)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/presets';
        return $this->GetSwitchboardData($endpoint);
    }

    /**
     * Plays a preset on the product.
     *
     * @param string $ProductID
     * @param int $PresetID
     * Identifer for a product preset. A preset identifier is an integer between 1 and 6, inclusive.
     *
     * @return false|string
     */
    public function PlayPreset(string $ProductID, int $PresetID)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/presets/' . $PresetID . '/play';
        $postfields = '';
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    /**
     * Plays an audio notification.
     *
     * @param string $ProductID
     * @param string $AudioUrl
     * @param int $VolumeOverride
     * @return false|string
     */
    public function PlayAudioNotification(string $ProductID, string $AudioUrl, int $VolumeOverride = 0)
    {
        $endpoint = 'https://partners.api.bose.io/products/' . $ProductID . '/content/notify';
        $postfields = json_encode(['url' => $AudioUrl]);
        if ($VolumeOverride != 0) {
            $postfields = json_encode(['url' => $AudioUrl, 'volumeOverride' => $VolumeOverride]);
        }
        return $this->PostSwitchboardData($endpoint, $postfields);
    }

    //#################### GET

    private function GetSwitchboardData(string $Endpoint)
    {
        $this->SendDebug(__FUNCTION__, $Endpoint, 0);
        $token = $this->FetchAccessToken();
        $timeout = round($this->ReadPropertyInteger('Timeout') / 1000);
        $body = '';
        // Send data to endpoint
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => $Endpoint,
            CURLOPT_HEADER          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_CONNECTTIMEOUT  => $timeout,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json',
                'X-API-Version: ' . $this->apiVersion,
                'X-ApiKey: ' . $this->apiKey,
                'Authorization: Bearer ' . $token]]);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
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

    //#################### POST

    private function PostSwitchboardData(string $Endpoint, string $Postfields)
    {
        $this->SendDebug(__FUNCTION__, $Endpoint, 0);
        $token = $this->FetchAccessToken();
        $timeout = round($this->ReadPropertyInteger('Timeout') / 1000);
        $body = '';
        // Send data to endpoint
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             => $Endpoint,
            CURLOPT_HEADER          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_CONNECTTIMEOUT  => $timeout,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json',
                'X-API-Version: ' . $this->apiVersion,
                'X-ApiKey: ' . $this->apiKey,
                'Authorization: Bearer ' . $token],
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $Postfields,
        ]);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 202:  # Accepted
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
