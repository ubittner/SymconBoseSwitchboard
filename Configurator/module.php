<?php

/** @noinspection PhpUnused */
/** @noinspection DuplicatedCode */

/*
 * @module      Bose Switchboard Configurator
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
 *              Bose Switchboard Configurator
 *             	{CC1A5C88-3952-479E-495C-3F6586634EBC}
 */

declare(strict_types=1);

include_once __DIR__ . '/../libs/constants.php';

class BoseSwitchboardConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        //Register property
        $this->RegisterPropertyInteger('CategoryID', 0);
        //Connect to splitter
        $this->ConnectParent(BOSE_SWITCHBOARD_SPLITTER_GUID);
    }

    public function Destroy()
    {
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
        $values = [];
        $existingDevices = $this->GetExistingDevices();
        if (!empty($existingDevices)) {
            $this->SendDebug(__FUNCTION__, $existingDevices, 0);
            $existingDevices = json_decode($existingDevices, true);
            if (array_key_exists('results', $existingDevices)) {
                $existingDevices = $existingDevices['results'];
                $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
                if (!empty($existingDevices)) {
                    foreach ($existingDevices as $key => $device) {
                        if (array_key_exists('productType', $device)) {
                            $productID = $device['productID'];
                            $instanceID = $this->GetDeviceInstanceID($productID);
                            $values[] = [
                                'ProductID'        => $productID,
                                'ProductName'      => $device['productName'],
                                'ProductType'      => $device['productType'],
                                'ConnectionStatus' => $device['connectionStatus'],
                                'instanceID'       => $instanceID,
                                'create'           => [
                                    'moduleID'      => BOSE_SWITCHBOARD_DEVICE_GUID,
                                    'name'          => $device['productName'],
                                    'configuration' => [
                                        'ProductID'   => (string) $productID,
                                        'ProductName' => (string) $device['productName'],
                                        'ProductType' => (string) $device['productType']
                                    ],
                                    'location' => $location
                                ]
                            ];
                        }
                    }
                }
            }
        }
        $formData['actions'][0]['values'] = $values;
        return json_encode($formData);
    }

    ################### Private

    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    private function GetCategoryPath(int $CategoryID): array
    {
        if ($CategoryID === 0) {
            return [];
        }
        $path[] = IPS_GetName($CategoryID);
        $parentID = IPS_GetObject($CategoryID)['ParentID'];
        while ($parentID > 0) {
            $path[] = IPS_GetName($parentID);
            $parentID = IPS_GetObject($parentID)['ParentID'];
        }
        return array_reverse($path);
    }

    private function GetExistingDevices(): string
    {
        if (!$this->HasActiveParent()) {
            return '';
        }
        $data = [];
        $buffer = [];
        $data['DataID'] = BOSE_SWITCHBOARD_SPLITTER_DATA_GUID;
        $buffer['Command'] = 'ListProducts';
        $buffer['Params'] = '';
        $data['Buffer'] = $buffer;
        $data = json_encode($data);
        return $this->SendDataToParent($data);
    }

    private function GetDeviceInstanceID($DeviceUID): int
    {
        $instanceID = 0;
        $instanceIDs = IPS_GetInstanceListByModuleID(BOSE_SWITCHBOARD_DEVICE_GUID);
        foreach ($instanceIDs as $id) {
            if (IPS_GetProperty($id, 'ProductID') == $DeviceUID) {
                $instanceID = $id;
            }
        }
        return $instanceID;
    }
}