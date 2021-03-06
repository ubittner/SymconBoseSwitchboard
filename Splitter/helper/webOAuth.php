<?php

/** @noinspection PhpUnused */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait BOSESB_webOAuth
{
    private $oauthIdentifier = 'bose_switchboard';
    private $oauthServer = 'oauth.ipmagic.de';

    /**
     * This function will be called by the register button on the property page!
     */
    public function Register()
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        //Return everything which will open the browser
        return 'https://' . $this->oauthServer . '/authorize/' . $this->oauthIdentifier . '?username=' . urlencode(IPS_GetLicensee());
    }

    public function RequestStatus()
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        echo $this->FetchData('https://' . $this->oauthServer . '/forward');
    }

    #################### Protected

    /**
     * This function will be called by the OAuth control. Visibility should be protected!
     */
    protected function ProcessOAuthData()
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        //Lets assume requests via GET are for code exchange.
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (!isset($_GET['code'])) {
                die('Authorization Code expected');
            }
            $refreshToken = $this->FetchRefreshToken($_GET['code']);
            $this->SendDebug(__FUNCTION__, "OK! Let's save the Refresh Token permanently", 0);
            $this->WriteAttributeString('RefreshToken', $refreshToken);
        } else {
            //Just print raw post data!
            echo file_get_contents('php://input');
        }
    }

    #################### Private

    /**
     * Registers the WebOAuth.
     *
     * @param $WebOAuth
     */
    private function RegisterWebOAuth($WebOAuth)
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $ids = IPS_GetInstanceListByModuleID(CORE_WEBOAUTH_GUID);
        if (count($ids) > 0) {
            $clientIDs = json_decode(IPS_GetProperty($ids[0], 'ClientIDs'), true);
            $found = false;
            foreach ($clientIDs as $index => $clientID) {
                if ($clientID['ClientID'] == $WebOAuth) {
                    if ($clientID['TargetID'] == $this->InstanceID) {
                        return;
                    }
                    $clientIDs[$index]['TargetID'] = $this->InstanceID;
                    $found = true;
                }
            }
            if (!$found) {
                $clientIDs[] = ['ClientID' => $WebOAuth, 'TargetID' => $this->InstanceID];
                $this->SendDebug(__FUNCTION__, 'WebOAuth was successfully registered', 0);
            }
            IPS_SetProperty($ids[0], 'ClientIDs', json_encode($clientIDs));
            IPS_ApplyChanges($ids[0]);
        }
    }

    /**
     * Unregisters the WebOAuth.
     *
     * @param $WebOAuth
     */
    private function UnregisterWebOAuth($WebOAuth)
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $ids = IPS_GetInstanceListByModuleID(CORE_WEBOAUTH_GUID);
        if (count($ids) > 0) {
            $clientIDs = json_decode(IPS_GetProperty($ids[0], 'ClientIDs'), true);
            $found = false;
            $index = null;
            foreach ($clientIDs as $key => $clientID) {
                if ($clientID['ClientID'] == $WebOAuth) {
                    if ($clientID['TargetID'] == $this->InstanceID) {
                        $found = true;
                        $index = $key;
                        break;
                    }
                }
            }
            if ($found === true && !is_null($index)) {
                array_splice($clientIDs, $index, 1);
                IPS_SetProperty($ids[0], 'ClientIDs', json_encode($clientIDs));
                IPS_ApplyChanges($ids[0]);
                $this->SendDebug(__FUNCTION__, 'WebOAuth was successfully registered', 0);
            }
        }
    }

    /**
     * Fetches the Refresh Token.
     *
     * @param $Code
     * @return string
     */
    private function FetchRefreshToken($Code): string
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $this->SendDebug(__FUNCTION__, 'Use Authentication Code to get our precious Refresh Token!', 0);
        //Exchange our Authentication Code for a permanent Refresh Token and a temporary Access Token
        $options = [
            'http' => [
                'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'        => 'POST',
                'content'       => http_build_query(['code' => $Code]),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents('https://' . $this->oauthServer . '/access_token/' . $this->oauthIdentifier, false, $context);
        //Check result
        if ((strpos($http_response_header[0], '200') === false)) {
            $this->SendDebug(__FUNCTION__, 'Error: ' . $http_response_header[0] . PHP_EOL . $result, 0);
            return '';
        }
        $data = json_decode($result);
        if (!isset($data->token_type) || $data->token_type != 'Bearer') {
            die('Bearer Token expected');
        }
        //Save temporary access token
        $this->FetchAccessToken($data->access_token, time() + $data->expires_in);
        return $data->refresh_token;
    }

    /**
     * Fetches the Access Token.
     *
     * @param string $AccessToken
     * @param int $Expires
     * @return string
     */
    private function FetchAccessToken($AccessToken = '', $Expires = 0): string
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        //Exchange our Refresh Token for a temporary Access Token
        if ($AccessToken == '' && $Expires == 0) {
            //Check if we already have a valid Token in cache
            $data = $this->GetBuffer('AccessToken');
            if ($data != '') {
                $data = json_decode($data);
                if (time() < $data->Expires) {
                    $this->SendDebug(__FUNCTION__, 'OK! Access Token is valid until ' . date('d.m.y H:i:s', $data->Expires), 0);
                    return $data->Token;
                }
            }
            //If we slipped here we need to fetch the access token
            $this->SendDebug(__FUNCTION__, 'Use Refresh Token to get new Access Token!', 0);
            $options = [
                'http' => [
                    'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'method'        => 'POST',
                    'content'       => http_build_query(['refresh_token' => $this->ReadAttributeString('RefreshToken')]),
                    'ignore_errors' => true
                ]
            ];
            $context = stream_context_create($options);
            $result = file_get_contents('https://' . $this->oauthServer . '/access_token/' . $this->oauthIdentifier, false, $context);
            //Check result
            if ((strpos($http_response_header[0], '200') === false)) {
                $this->SendDebug(__FUNCTION__, 'Error: ' . $http_response_header[0] . PHP_EOL . $result, 0);
                return '';
            }
            $data = json_decode($result);
            if (!isset($data->token_type) || $data->token_type != 'Bearer') {
                $this->SendDebug(__FUNCTION__, 'Bearer Token expected!', 0);
                return '';
            }
            //Update parameters to properly cache it in the next step
            $AccessToken = $data->access_token;
            $Expires = time() + $data->expires_in;
            //Update Refresh Token if we received one! (This is optional)
            if (isset($data->refresh_token)) {
                $this->SendDebug(__FUNCTION__, "NEW! Let's save the updated Refresh Token permanently.", 0);
                $this->WriteAttributeString('RefreshToken', $data->refresh_token);
            }
        }
        $this->SendDebug(__FUNCTION__, 'CACHE! New Access Token is valid until ' . date('d.m.y H:i:s', $Expires), 0);
        $this->SetBuffer('AccessToken', json_encode(['Token' => $AccessToken, 'Expires' => $Expires]));
        return $AccessToken;
    }

    /**
     * Fetches the data.
     *
     * @param $Url
     * @return false|string
     */
    private function FetchData($Url)
    {
        $this->SendDebug(__FUNCTION__, 'Method executed (' . microtime(true) . ')', 0);
        $opts = [
            'http' => [
                'method'        => 'POST',
                'header'        => 'Authorization: Bearer ' . $this->FetchAccessToken() . "\r\n" . 'Content-Type: application/json' . "\r\n",
                'content'       => '{"JSON-KEY":"THIS WILL BE LOOPED BACK AS RESPONSE!"}',
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($opts);
        $result = file_get_contents($Url, false, $context);
        if ((strpos($http_response_header[0], '200') === false)) {
            echo $http_response_header[0] . PHP_EOL . $result;
            return false;
        }
        return $result;
    }
}
