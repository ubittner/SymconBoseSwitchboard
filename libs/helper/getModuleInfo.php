<?php

declare(strict_types=1);

trait libs_helper_getModuleInfo
{
    private function GetModuleInfo(string $ModuleGUID)
    {
        $moduleInfo = [];
        $lib = IPS_GetLibrary(BOSE_SWITCHBOARD_LIBRARY_GUID);
        $module = IPS_GetModule($ModuleGUID);
        $moduleInfo['name'] = $module['ModuleName'];
        $moduleInfo['version'] = $lib['Version'] . '-' . $lib['Build'];
        $moduleInfo['date'] = date('d.m.Y', $lib['Date']);
        $moduleInfo['time'] = date('H:i', $lib['Date']);
        $moduleInfo['developer'] = $lib['Author'];
        return $moduleInfo;
    }
}