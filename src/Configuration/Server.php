<?php

namespace Onigoetz\Deployer\Configuration;

class Server extends ConfigurationContainer
{
    public static $defaultPassword = null;

    public function getHost()
    {
        return $this->getValueOrFail('host', 'no host found for this server');
    }

    public function getUsername()
    {
        return $this->getValueOrFail('username', 'no username found for this server');
    }

    public function getPassword()
    {
        return $this->getValueOrDefault('password', self::$defaultPassword);
    }

    public function isValid()
    {
        try {
            $this->getHost();
            $this->getUsername();
        } catch (\LogicException $e) {
            return false;
        }

        return true;
    }
}
