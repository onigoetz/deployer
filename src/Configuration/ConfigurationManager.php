<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 21:22
 */

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ConfigurationContainer;

class ConfigurationManager
{
    protected $configurations = array();

    public $logs = array();

    /**
     * @param string $type
     * @param string $key
     *
     * @return ConfigurationContainer
     * @throws \LogicException
     */
    public function get($type, $key)
    {
        if (!array_key_exists($type, $this->configurations) || !array_key_exists($key, $this->configurations[$type])) {
            throw new \LogicException("No item of type '$type' with name '$key' in the configuration manager");
        }

        return $this->configurations[$type][$key];
    }

    /**
     * @param ConfigurationContainer $value
     */
    public function set(ConfigurationContainer $value)
    {
        $this->configurations[$value->getContainerType()][$value->getName()] = $value;
    }

    public function setDefaultDirectories(Directories $value)
    {
        $this->set($value);
    }

    /**
     * @return Directories
     */
    public function getDefaultDirectories()
    {
        return $this->get('directories', 'default');
    }

    public function log($message)
    {
        $this->logs[] = $message;
    }

    public function getLogs()
    {
        return $this->logs;
    }
}
