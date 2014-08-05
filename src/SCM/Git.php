<?php

namespace Onigoetz\Deployer\SCM;

use Onigoetz\Deployer\Registry;

class Git extends SCM
{

    protected function getCommand()
    {
        $ssh = Registry::get('ssh');

        $git_command = str_replace("\n", "", $ssh->exec('which git'));
        if ($git_command == '') {
            //TODO :: ERROR
        }

        return $git_command;
    }

    public function cloneCommand(array $directories)
    {
        $config_deploy = Registry::get('config_deploy');

        $clone_command = $this->getCommand() . ' clone ';

        if ($config_deploy['scm']['options']['submodules']) {
            $clone_command .= ' --recursive';
        }

        $clone_command .= ' -b ' . $config_deploy['scm']['branch'];

        $clone_command .= ' "' . $config_deploy['scm']['final_url'] . '"';

        $clone_command .= ' "' . $directories['snapshot'] . '"';

        return $clone_command;
    }

    public function getFinalUrl()
    {
        $config = Registry::get('config');
        $config_deploy = $config['deploy'];

        //password provided ?
        if (!array_key_exists('username', $config_deploy['scm'])) {
            $config_deploy['scm']['username'] = ask_password('Your repository username');
        }

        //password provided ?
        if (!array_key_exists('password', $config_deploy['scm'])) {
            $config_deploy['scm']['password'] = ask_password('Your repository password');
        }

        //HTTPS username:password@
        return str_replace(
            'https://',
            'https://' . $config_deploy['scm']['username'] . ':' . $config_deploy['scm']['password'] . '@',
            $config_deploy['scm']['repository']
        );
    }
}
