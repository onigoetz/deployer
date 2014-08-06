<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 20:50
 */

namespace Onigoetz\Deployer\Configuration\Containers;


abstract class InheritingConfigurationContainer extends ConfigurationContainer
{
    /**
     * @var InheritingConfigurationContainer
     */
    protected $parent;

    public function __construct(array $data, InheritingConfigurationContainer $parent = null)
    {
        parent::__construct($data);

        $this->parent = $parent;
    }

    protected function getValueOrFail($key, $error_message)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if ($this->parent) {
            return $this->parent->getValueOrFail($key, $error_message);
        }

        throw new \LogicException($error_message);
    }

    protected function getValueOrDefault($key, $default)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if ($this->parent) {
            return $this->parent->getValueOrDefault($key, $default);
        }

        return $default;
    }
}
