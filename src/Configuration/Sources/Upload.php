<?php

namespace Onigoetz\Deployer\Configuration\Sources;

use Onigoetz\Deployer\Configuration\Source;

class Upload extends Source
{
    public static $defaultInclude = [];
    public static $defaultExclude = [];

    public function getInclude()
    {
        return $this->getValueOrDefault('include', self::$defaultInclude);
    }

    public function getExclude()
    {
        return $this->getValueOrDefault('exclude', self::$defaultExclude);
    }
}
