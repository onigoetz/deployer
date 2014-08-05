<?php

namespace Onigoetz\Deployer;

use Onigoetz\Deployer\Command\DeployCommand;
use Onigoetz\Deployer\Command\RollbackCommand;
use Symfony\Component\Console\Application;

class Init
{
    public static $init = false;

    public static function bootstrap($config)
    {
        if (self::$init) {
            return true;
        }

        include dirname(__DIR__) . '/functions.php';

        define('ERROR_ENVIRONMENT_NOT_AVAILABLE', 1);
        define('ERROR_SERVER_LOGIN_FAILED', 2);
        define('ERROR_CANNOT_CREATE_DIRECTORY_DEPLOY', 3);
        define('ERROR_CANNOT_CREATE_DIRECTORY_SNAPSHOTS', 4);

        Registry::set('config', $config);

        self::$init = true;
    }

    public static function run($config)
    {
        self::bootstrap($config);

        //TODO :: Check configuration

        $application = new Application();
        $application->add(new DeployCommand);
        $application->add(new RollbackCommand);
        $application->run();

        exit(0);
    }
}
