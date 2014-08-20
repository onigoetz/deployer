<?php

namespace Onigoetz\Deployer\SCM;

class Git extends SCM
{
    public function getCommand(\Net_SFTP $ssh)
    {
        $git_command = str_replace("\n", "", $ssh->exec('which git'));
        if ($git_command == '') {
            throw new \Exception('the git command wasn\'t found on this server');
        }

        return $git_command;
    }

    public function cloneCommand($command, $repository, $binary)
    {
        $clone_command = $command . ' clone ';

        if ($this->environment->getSource()->getSubmodules()) {
            $clone_command .= ' --recursive';
        }

        $clone_command .= ' -b ' . $this->environment->getSource()->getBranch();
        $clone_command .= ' "' . $repository . '"';
        $clone_command .= ' "' . $binary . '"';

        return $clone_command;
    }
}
