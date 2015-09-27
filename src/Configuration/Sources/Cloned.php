<?php

namespace Onigoetz\Deployer\Configuration\Sources;

use Onigoetz\Deployer\Configuration\Source;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

//the name of the class is "cloned" as "clone" is a reserved keyword
class Cloned extends Source
{
    public static $defaultType = 'git';
    public static $defaultBranch = 'master';
    public static $defaultSubmodules = false;

    public function getType()
    {
        return $this->getValueOrDefault('type', self::$defaultType);
    }

    public function getBranch()
    {
        return $this->getValueOrDefault('branch', self::$defaultBranch);
    }

    public function getSubmodules()
    {
        return $this->getValueOrDefault('submodules', self::$defaultSubmodules);
    }

    public function getUsername()
    {
        return $this->getValueOrDefault('username', null);
    }

    public function setUsername($username)
    {
        $this->data['username'] = $username;
    }

    public function getPassword()
    {
        return $this->getValueOrDefault('password', null);
    }

    public function setPassword($password)
    {
        $this->data['password'] = $password;
    }

    public function getFinalUrl(DialogHelper $dialog, OutputInterface $output)
    {
        $regex = '/^((?P<scheme>https?):\\/)?\\/?((?P<username>.*?)(:(?P<password>.*?)|)@)?(?P<uri>.*)/';
        preg_match($regex, $this->getPath(), $matches);

        //username provided ?
        if ($matches['username']) {
            $username = $matches['username'];
        } elseif (!$username = $this->getUsername()) {
            $username = $dialog->ask($output, 'Your repository username: ');
            $this->setUsername($username);
        }

        //password provided ?
        if ($matches['password']) {
            $password = $matches['password'];
        } elseif (!$password = $this->getPassword()) {
            $password = $dialog->askHiddenResponse($output, 'Your repository password: ', false);
            $this->setPassword($password);
        }

        //HTTP(S)? username:password@
        return "{$matches['scheme']}://$username:$password@{$matches['uri']}";
    }
}
