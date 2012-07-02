<?php

namespace Deployer\SCM;

use Deployer\Registry;

class Git extends SCM {

    function get_command(){
        $ssh = Registry::get('ssh');
        
        $git_command = str_replace(LN, "", $ssh->exec('which git'));
        if($git_command == ''){
            //TODO :: ERROR
        }
        
        return $git_command;
    }
    
    function clone_command($directories){
        $config = Registry::get('config');

        $clone_command = $this->get_command().' clone ';

        if($config['scm']['options']['submodules']){
            $clone_command .= ' --recursive';
        }

        $clone_command .= ' -b '.$config['scm']['branch'];

        $clone_command .= ' "'.$config['scm']['final_url'].'"';

        $clone_command .= ' "'.$directories['snapshot'].'"';
    }
    
    function final_url(){
        
        $config = Registry::get('config');
        $config_deploy = $config['deploy'];
        
        //password provided ?
        if(!array_key_exists('username', $config_deploy['scm'])){
            $config_deploy['scm']['username'] = ask_password('Your repository username');
        }

        //password provided ?
        if(!array_key_exists('password', $config_deploy['scm'])){
            $config_deploy['scm']['password'] = ask_password('Your repository password');
        }

        //HTTPS username:password@
        return str_replace('https://', 'https://'.$config_deploy['scm']['username'].':'.$config_deploy['scm']['password'].'@', $config_deploy['scm']['repository']);

    }
}