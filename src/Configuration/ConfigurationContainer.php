<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 05.08.14
 * Time: 20:50
 */

namespace Onigoetz\Deployer\Configuration;


abstract class ConfigurationContainer
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function getValueOrFail($key, $error_message)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        throw new \LogicException($error_message);
    }

    protected function getValueOrDefault($key, $default)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    abstract public function isValid();
}
