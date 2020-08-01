<?php

/** @noinspection PhpUnused */
/** @noinspection DuplicatedCode */

/*
 * @module      Bose Switchboard Discovery
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
 * @see         https://github.com/ubittner/SymconBoseSwitchboard/Discovery
 *
 * @guids       Library
 *              {7101C0B6-7A8D-1FE2-A427-8DDCA26C3244}
 *
 *              Bose Switchboard Discovery
 *             	{C7419D17-AFAB-7ADB-0734-8F39D9E5ADE1}
 */

declare(strict_types=1);

include_once __DIR__ . '/../libs/helper/autoload.php';

class BoseSwitchboardDiscovery extends IPSModule
{
    public function Create()
    {
        // Never delete this line!
        parent::Create();
        // Properties
        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyInteger('CategoryID', 0);
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
        $module = IPS_GetModule(BOSE_SWITCHBOARD_DISCOVERY_GUID);
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
        $existingDevices = $this->DiscoverDevices();
        if (!empty($existingDevices)) {
            foreach ($existingDevices as $device) {
                $productID = $device['productID'];
                $instanceID = $this->GetDeviceInstances($productID);
                $location = $this->GetCategoryPath($this->ReadPropertyInteger(('CategoryID')));
                $values[] = [
                    'IP'          => $device['ip'],
                    'ProductName' => $device['productName'],
                    'ProductType' => $device['productType'],
                    'ProductID'   => $productID,
                    'instanceID'  => $instanceID,
                    'create'      => [
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
        $formData['actions'][0]['values'] = $values;
        return json_encode($formData);
    }

    //#################### Private

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

    /** @noinspection PhpUndefinedFunctionInspection */
    private function DiscoverDevices()
    {
        $ids = IPS_GetInstanceListByModuleID(CORE_DNS_SD_GUID);
        $devices = ZC_QueryServiceType($ids[0], '_bose-passport._tcp.', '');
        $this->SendDebug(__FUNCTION__, print_r($devices, true), 0);
        $existingDevices = [];
        if (!empty($devices)) {
            foreach ($devices as $device) {
                $data = [];
                $deviceInfos = ZC_QueryService($ids[0], $device['Name'], '_bose-passport._tcp.', 'local.');
                $this->SendDebug(__FUNCTION__, print_r($deviceInfos, true), 0);
                if (!empty($deviceInfos)) {
                    foreach ($deviceInfos as $info) {
                        if (empty($info['IPv4'])) {
                            $data['ip'] = $info['IPv6'][0];
                        } else {
                            $data['ip'] = $info['IPv4'][0];
                        }
                        $data['productName'] = (string) str_replace('._bose-passport._tcp.local', '', $info['Name']);
                        if (array_key_exists('TXTRecords', $info)) {
                            $txtRecords = $info['TXTRecords'];
                            foreach ($txtRecords as $record) {
                                if (strpos($record, 'PNAME=') !== false) {
                                    $data['productType'] = (string) str_replace('PNAME=', '', $record);
                                }
                                if (strpos($record, 'GUID=') !== false) {
                                    $data['productID'] = (string) str_replace('GUID=', '', $record);
                                }
                            }
                        }
                        array_push($existingDevices, $data);
                    }
                }
            }
        }
        return $existingDevices;
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