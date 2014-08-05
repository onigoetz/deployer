<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 21:51
 */

namespace Onigoetz\Deployer\Configuration\Sources;

use Onigoetz\Deployer\Configuration\Source;

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
}
