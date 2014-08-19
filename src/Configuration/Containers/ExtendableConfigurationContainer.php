<?php

namespace Onigoetz\Deployer\Configuration\Containers;

abstract class ExtendableConfigurationContainer extends InheritingConfigurationContainer
{
    /**
     * Loads the parent instance specified in the 'extends' key
     */
    protected function loadParent()
    {
        if (!$this->parent && array_key_exists('extends', $this->data)) {
            $this->parent = $this->manager->get($this->getContainerType(), $this->data['extends']);
        }
    }

    /**
     * {@inheritdoc}
     * @param string $key
     */
    protected function getValueOrFail($key, $error_message)
    {
        $this->loadParent();
        return parent::getValueOrFail($key, $error_message);
    }

    /**
     * {@inheritdoc}
     * @param string $key
     */
    protected function getValueOrDefault($key, $default)
    {
        $this->loadParent();
        return parent::getValueOrDefault($key, $default);
    }
}
