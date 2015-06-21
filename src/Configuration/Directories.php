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
        return $this->getRoot() . '/' . $this->getValueOrDefault('binaries', self::$defaultBinaries);
    }

    public function getBinaryName()
    {
        return $this->getValueOrDefault('binary_name', self::$defaultBinaryName);
    }

    public function getDeploy()
    {
        return $this->getRoot() . '/' . $this->getValueOrDefault('deploy', self::$defaultDeploy);
    }

    public function getNewBinaryName()
    {
        return $this->getBinaries() . '/' . strftime($this->getBinaryName());
    }

    /**
     * {@inheritdoc}
     */
    public function checkValidity()
    {
        $this->getRoot();

        return true;
    }
}
