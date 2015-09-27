<?php

namespace Onigoetz\Deployer\Configuration\Containers;

use Onigoetz\Deployer\Configuration\ConfigurationManager;

abstract class ConfigurationContainer
{
    /**
     * @var string The name of the element
     */
    protected $name;

    /**
     * @var array The data for this container
     */
    protected $data;

    /**
     * @var \Onigoetz\Deployer\Configuration\ConfigurationManager
     */
    protected $manager;

    public function __construct($name, array $data, ConfigurationManager $manager)
    {
        $this->name = $name;
        $this->data = $data;
        $this->manager = $manager;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the configuration manager's key
     *
     * @return string
     */
    public function getContainerType()
    {
        return strtolower(implode('', array_slice(explode('\\', get_class($this)), -1)));
    }

    /**
     * Get the value or throw an exception
     *
     * @param string $key
     * @param $error_message
     * @throws \LogicException
     * @return mixed
     */
    protected function getValueOrFail($key, $error_message)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        throw new \LogicException($error_message);
    }

    /**
     * Get the value or return the default
     *
     * @param string $key
     * @param $default
     * @return mixed
     */
    protected function getValueOrDefault($key, $default)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Tests validity
     *
     * @return bool
     */
    public function isValid()
    {
        try {
            return $this->checkValidity();
        } catch (\LogicException $e) {
            $this->manager->log($e->getMessage());

            return false;
        }
    }

    /**
     * Internal validity check
     *
     * @throws \LogicException
     * @return bool
     */
    abstract public function checkValidity();
}
