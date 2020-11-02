<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class SymconBoseSwitchboardValidationTest extends TestCaseSymconValidation
{
    public function testValidateSymconBoseSwitchboard(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateConfiguratorModule(): void
    {
        $this->validateModule(__DIR__ . '/../Configurator');
    }

    public function testValidateDeviceModule(): void
    {
        $this->validateModule(__DIR__ . '/../Device');
    }

    public function testValidateDiscoveryModule(): void
    {
        $this->validateModule(__DIR__ . '/../Discovery');
    }

    public function testValidateSplitterModule(): void
    {
        $this->validateModule(__DIR__ . '/../Splitter');
    }
}