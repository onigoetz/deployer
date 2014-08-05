<?php

namespace Onigoetz\Deployer\Configuration;

class Directories extends InheritingConfigurationContainer
{
    public static $defaultBinaries = 'binaries';
    public static $defaultBinaryName = '%G-%m-%d_%H-%M';
    public static $defaultDeploy = 'www';

    public function getRoot()
    {
        return $this->getValueOrFail('root', 'no root server found');
    }

    public function getBinaries()
    {
        return $this->getValueOrDefault('binaries', self::$defaultBinaries);
    }

    public function getBinaryName()
    {
        return $this->getValueOrDefault('binary_name', self::$defaultBinaryName);
    }

    public function getDeploy()
    {
        return $this->getValueOrDefault('deploy', self::$defaultDeploy);
    }

    public function isValid()
    {
        try {
            $this->getRoot();
        } catch (\LogicException $e) {
            return false;
        }

        return true;
    }
}
