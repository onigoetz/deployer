<?php

namespace Onigoetz\Deployer;

use Onigoetz\Deployer\Command\DeployCommand;
use Onigoetz\Deployer\Command\RollbackCommand;
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Symfony\Component\Console\Application;

class Init
{
    public static function findConfiguration()
    {
        $current = __DIR__;
        $previous = null;
        while (true) {
            $current = dirname($current);

            if (is_dir("$current/.deployer")) {
                return $current;
            }

            if ($previous == $current) {
                throw new \Exception('Could not find a deployer configuration folder');
            }

            $previous = $current;
        }
    }

    public static function run()
    {
        $dir = self::findConfiguration();

        $config_folder = "$dir/.deployer";

        $configuration = array(
            'directories' => include "$config_folder/directories.php",
            'servers' => include "$config_folder/servers.php",
            'sources' =>include "$config_folder/sources.php",
            'tasks' => include "$config_folder/tasks.php",
            'environments' => include "$config_folder/environments.php",
        );

        $manager = ConfigurationManager::create($configuration);

        $application = new Application();
        $application->add(new DeployCommand($manager));
        $application->add(new RollbackCommand($manager));
        $application->run();

        exit(0);
    }
}
