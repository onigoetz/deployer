<?php

namespace Deployer;

use Deployer\Command\DeployCommand;
use Deployer\Registry;
use Symfony\Component\Console\Application;

class Init {

    public static function bootstrap($config) {

        if (!defined('VERBOSE')) {
            define('VERBOSE', false);
        }

        define('ERROR_ENVIRONMENT_NOT_AVAILABLE', 1);
        define('ERROR_SERVER_LOGIN_FAILED', 2);
        define('ERROR_CANNOT_CREATE_DIRECTORY_DEPLOY', 3);
        define('ERROR_CANNOT_CREATE_DIRECTORY_SNAPSHOTS', 4);

        error_reporting(E_ALL);

        Registry::set('config', $config);

        //TODO :: Check configuration

        $application = new Application();
        $application->add(new DeployCommand);
        $application->run();


        exit(0);
    }

}

