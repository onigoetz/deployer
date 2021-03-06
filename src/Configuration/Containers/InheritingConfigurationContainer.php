<?php

namespace Onigoetz\Deployer\Configuration\Containers;

use Onigoetz\Deployer\Configuration\ConfigurationManager;

abstract class InheritingConfigurationContainer extends ConfigurationContainer
{
    /**
     * @var InheritingConfigurationContainer
     */
    protected $parent;

    public function __construct($name, array $data, ConfigurationManager $manager, self $parent = null)
    {
        parent::__construct($name, $data, $manager);

        $this->parent = $parent;
    }

    /**
     * Get the value or throw an exception
     * Will check the parent before the exception is thrown
     *
     * @param $key
     * @param $errorMessage
     * @throws \LogicException
     * @return mixed
     */
    protected function getValueOrFail($key, $errorMessage)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if ($this->parent) {
            return $this->parent->getValueOrFail($key, $errorMessage);
        }

        throw new \LogicException($errorMessage);
    }

    /**
     * Get the value or return the default
     * Will check the parent before the default is returned
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    protected function getValueOrDefault($key, $default)
    {
        try {
            return $this->getValueOrFail($key, '');
        } catch (\LogicException $e) {
            return $default;
        }
    }
}
