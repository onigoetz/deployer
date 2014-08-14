<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 21:51
 */

namespace Onigoetz\Deployer\Configuration\Sources;

use Onigoetz\Deployer\Configuration\Source;
use Symfony\Component\Console\Output\OutputInterface;

//the name of the class is "cloned" as "clone" is a reserved keyword
class Cloned extends Source
{
    protected $resolvedUrl;

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

    public function getPassword()
    {
        return $this->getValueOrDefault('password', null);
    }

    public function getFinalUrl($dialog, OutputInterface $output)
    {
        if ($this->resolvedUrl) {
            return $this->resolvedUrl;
        }

        //username provided ?
        if (!$username = $this->getUsername()) {
            $username = $dialog->ask($output, 'Your repository password');
        }

        //password provided ?
        if (!$password = $this->getPassword()) {
            $password = $dialog->askHiddenResponse($output, 'Your repository password', false);
        }

        //HTTPS username:password@
        return $this->resolvedUrl = str_replace('https://', "https://$username:$password@", $this->getPath());
    }
}
