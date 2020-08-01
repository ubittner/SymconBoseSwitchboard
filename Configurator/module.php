<?php

/** @noinspection PhpUnused */
/** @noinspection DuplicatedCode */

/*
 * @module      Bose Switchboard Configurator
 *
 * @prefix      BSBC
 *
 * @file        module.php
 *
 * @author      Ulrich Bittner
 * @copyright   (c) 2020
 * @license     CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @see         https://github.com/ubittner/SymconBoseSwitchboard/Configurator
 *
 * @guids       Library
 *              {7101C0B6-7A8D-1FE2-A427-8DDCA26C3244}
 *
 *              Bose Switchboard Configurator
 *             	{CC1A5C88-3952-479E-495C-3F6586634EBC}
 */

declare(strict_types=1);

include_once __DIR__ . '/../libs/helper/autoload.php';

class BoseSwitchboardConfigurator extends IPSModule
{
    public function Create()
    {
        // Never delete this line!
        parent::Create();
        // Properties
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyInteger('CategoryID', 0);
        // Connect to parent (Splitter)
        $this->ConnectParent(BOSE_SWITCHBOARD_SPLITTER_GUID);
    }

    public function Destroy()
    {
        // Never delete this line!
        parent::Destroy();
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
        $module = IPS_GetModule(BOSE_SWITCHBOARD_CONFIGURATOR_GUID);
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
                            $instanceID = $this->GetDeviceInstances($productID);
                            $values[] = [
                                'ProductID'        => $productID,
                                'ProductName'      => $device['productName'],
                                'ProductType'      => $device['productType'],
                                'ConnectionStatus' => $device['connectionStatus'],
                                'instanceID'       => $instanceID,
                                'create'           => [
                                    'moduleID'      => BOSE_SWITCHBOARD_DEVICE_GUID,
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

    //################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function GetCategoryPath(int $CategoryID)
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

    private function GetExistingDevices()
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

    private function GetDeviceInstances($DeviceUID)
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