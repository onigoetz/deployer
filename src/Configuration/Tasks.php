<?php

namespace Onigoetz\Deployer\Configuration;

use Onigoetz\Deployer\Configuration\Containers\ConfigurationContainer;

class Tasks extends ConfigurationContainer
{
    public function getTasks()
    {
        return $this->data;
    }

    public function isValid()
    {
        //TODO :: validate
        return true;
    }
}
