<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 21:51
 */

namespace Onigoetz\Deployer\Configuration\Sources;

use Onigoetz\Deployer\Configuration\Source;

class Upload extends Source
{
    public static $defaultInclude = array();
    public static $defaultExclude = array();

    public function getInclude()
    {
        return $this->getValueOrDefault('include', self::$defaultInclude);
    }

    public function getExclude()
    {
        return $this->getValueOrDefault('exclude', self::$defaultExclude);
    }
}
