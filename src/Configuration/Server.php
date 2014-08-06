<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ConfigurationContainer;

class Server extends ConfigurationContainer
{
    public static $defaultPassword = null;

    public function getHost()
    {
        return $this->getValueOrFail('host', "no 'host' specified in server '{$this->name}'");
    }

    public function getUsername()
    {
        return $this->getValueOrFail('username', "no 'username' specified in server '{$this->name}'");
    }

    public function getPassword()
    {
        return $this->getValueOrDefault('password', self::$defaultPassword);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        try {
            $this->getHost();
            $this->getUsername();
        } catch (\LogicException $e) {
            $this->manager->log($e->getMessage());
            return false;
        }

        return true;
    }
}
