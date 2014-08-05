<?php

namespace Onigoetz\Deployer\SCM;

abstract class SCM
{
    abstract protected function getCommand();

    abstract public function cloneCommand(array $options);
}
