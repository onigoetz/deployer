<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\InheritingConfigurationContainer;

class Directories extends InheritingConfigurationContainer
{
    public static $defaultBinaries = 'binaries';
    public static $defaultBinaryName = '%G-%m-%d_%H-%M';
    public static $defaultDeploy = 'www';

    public function getRoot()
    {
        return $this->getValueOrFail('root', "no root directory specified");
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

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        try {
            $this->getRoot();
        } catch (\LogicException $e) {
            $this->manager->log($e->getMessage());
            return false;
        }

        return true;
    }
}
