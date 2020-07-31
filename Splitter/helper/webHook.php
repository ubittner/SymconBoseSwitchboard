<?php

declare(strict_types=1);

trait BSBS_webHook
{
    /**
     * Used for the API Event Callback URL.
     */
    protected function ProcessHookData()
    {
        $this->SendDebug(__FUNCTION__ . ' Incoming Data: ', print_r($_SERVER, true), 0);
        // Get content
        $data = file_get_contents('php://input');
        $this->SendDebug(__FUNCTION__ . ' Data: ', $data, 0);
        // Send data to children
        $forwardData = [];
        $forwardData['DataID'] = CORE_WEBHOOK_DATA_GUID;
        $forwardData['Buffer'] = json_decode($data);
        $forwardData = json_encode($forwardData);
        $this->SendDebug(__FUNCTION__ . ' Forward Data: ', $forwardData, 0);
        // Prepared, but not used at the moment!
        // $this->SendDataToChildren($forwardData);
        $this->SendDebug(__FUNCTION__ . ' SendDataToChildren is not used at the moment!', $forwardData, 0);
    }

    /**
     * Registers a WebHook for:
     * Asynchronous event generated in response to a previous request.
     * This response is sent only to the client that created the original request.
     *
     * @param $WebHook
     */
    private function RegisterWebHook($WebHook)
    {
        $ids = IPS_GetInstanceListByModuleID(CORE_WEBHOOK_GUID);
        if (count($ids) > 0) {
            $hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
            $found = false;
            foreach ($hooks as $index => $hook) {
                if ($hook['Hook'] == $WebHook) {
                    if ($hook['TargetID'] == $this->InstanceID) {
                        return;
                    }
                    $hooks[$index]['TargetID'] = $this->InstanceID;
                    $found = true;
                }
            }
            if (!$found) {
                $hooks[] = ['Hook' => $WebHook, 'TargetID' => $this->InstanceID];
                $this->SendDebug(__FUNCTION__, 'WebHook was successfully registered', 0);
            }
            IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
            IPS_ApplyChanges($ids[0]);
        }
    }

    private function UnregisterWebHook($WebHook)
    {
        $ids = IPS_GetInstanceListByModuleID(CORE_WEBHOOK_GUID);
        if (count($ids) > 0) {
            $hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
            $found = false;
            $index = null;
            foreach ($hooks as $key => $hook) {
                if ($hook['Hook'] == $WebHook) {
                    if ($hook['TargetID'] == $this->InstanceID) {
                        $found = true;
                        $index = $key;
                        break;
                    }
                }
            }
            if ($found === true && !is_null($index)) {
                array_splice($hooks, $index, 1);
                IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
                IPS_ApplyChanges($ids[0]);
                $this->SendDebug(__FUNCTION__, 'WebHook was successfully unregistered', 0);
            }
        }
    }
}