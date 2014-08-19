<?php

namespace Onigoetz\Deployer\SCM;

use Onigoetz\Deployer\Configuration\Environment;

abstract class SCM
{
    protected $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    abstract public function getCommand(\Net_SFTP $ssh);

    abstract public function cloneCommand($command, $repository, $binary);
}
