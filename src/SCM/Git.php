<?php

namespace Onigoetz\Deployer\SCM;

class Git extends SCM
{
    public function getCommand(\Net_SFTP $ssh)
    {
        $gitCommand = str_replace("\n", '', $ssh->exec('which git'));
        if ($gitCommand == '') {
            throw new \Exception('the git command wasn\'t found on this server');
        }

        return $gitCommand;
    }

    public function cloneCommand($command, $repository, $binary)
    {
        $cloneCommand = $command . ' clone ';

        if ($this->environment->getSource()->getSubmodules()) {
            $cloneCommand .= ' --recursive';
        }

        $cloneCommand .= ' -b ' . $this->environment->getSource()->getBranch();
        $cloneCommand .= ' "' . $repository . '"';
        $cloneCommand .= ' "' . $binary . '"';

        return $cloneCommand;
    }
}
