<?php

namespace Deployer;

use Deployer\Command\DeployCommand;
use Deployer\Registry;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class Init {

    public static function bootstrap($config) {
        
        include dirname(__DIR__).'/functions.php';

        if (!defined('VERBOSE')) {
            define('VERBOSE', false);
        }
        
        define("LN", "\n");

        define('ERROR_ENVIRONMENT_NOT_AVAILABLE', 1);
        define('ERROR_SERVER_LOGIN_FAILED', 2);
        define('ERROR_CANNOT_CREATE_DIRECTORY_DEPLOY', 3);
        define('ERROR_CANNOT_CREATE_DIRECTORY_SNAPSHOTS', 4);

        error_reporting(E_ALL);

        Registry::set('config', $config);

        //TODO :: Check configuration

        $application = new Application();
        $application->add(new DeployCommand);
        
        
        $output = new ConsoleOutput();
        
        $style = new OutputFormatterStyle('green');
        $output->getFormatter()->setStyle('server', $style);
        
        $style = new OutputFormatterStyle('blue', null, array('bold'));
        $output->getFormatter()->setStyle('command', $style);

        $application->run(null, $output);

        exit(0);
    }

}

