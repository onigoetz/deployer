<?php

namespace Onigoetz\Deployer;

use ReflectionMethod;

class MethodCaller
{
    public function call($object, $method, $args)
    {
        if (!method_exists($object, $method)) {
            throw new \LogicException("Method '$method' doesn't exist");
        }

        $reflected = new ReflectionMethod(get_class($object), $method);
        $parameters = $reflected->getParameters();

        $arguments = [];
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $args)) {
                $arguments[$param->name] = $args[$param->name];
            } else {
                $arguments[$param->name] = ($param->isOptional()) ? $param->getDefaultValue() : null;
            }
        }

        return call_user_func_array([$object, $method], $arguments);
    }
}
