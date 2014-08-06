<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 21:22
 */

namespace Onigoetz\Deployer\Configuration;

class ConfigurationManager
{
    protected $configurations = array();

    /**
     * @param string $type
     * @param string $key
     *
     * @return Containers\ConfigurationContainer
     * @throws \LogicException
     */
    public function get($type, $key)
    {
        if (!array_key_exists($type, $this->configurations) || !array_key_exists($key, $this->configurations[$type])) {
            throw new \LogicException("no item of type '$type' with key '$key' in the configuration manager");
        }

        return $this->configurations[$type][$key];
    }

    public function set($key, $value)
    {
        $this->configurations[strtolower(get_class($value))][$key] = $value;
    }
}
