<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Environment;
use Onigoetz\Deployer\MethodCaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    const EXIT_CODE_ENVIRONMENT_NOT_AVAILABLE = 1;
    const EXIT_CODE_SERVER_LOGIN_FAILED = 2;
    const EXIT_CODE_CANNOT_CREATE_DIRECTORY_DEPLOY = 3;
    const EXIT_CODE_CANNOT_CREATE_DIRECTORY_SNAPSHOTS = 4;
    const EXIT_CODE_INVALID_CONFIGURATION = 5;

    protected $manager;

    public function __construct(ConfigurationManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function prepareDir($dir, $directories)
    {
        $dir = strtr($dir, $directories);

        if (strpos($dir, '/') !== 0) {
            $dir = $directories['{{root}}'] . '/' . $dir;
        }

        return $dir;
    }

    protected function runActions($runner, $actions, $output, $directories)
    {
        foreach ($actions as $description => $action) {
            $output->writeln($description);
            $this->runAction($runner, $action, $output, $directories);
        }
    }

    protected function runAction($runner, $action, $output, $directories)
    {
        $method = $action['action'];
        unset($action['action']);

        $parameters= [];
        foreach ($action as $key => $value) {
            $parameters[$key] = $this->prepareDir($value, $directories);
        }

        $response = (new MethodCaller)->call($runner, $method, $parameters);
        $output->writeln('<fg=green>' . $response . '</fg=green>');
    }

    protected function allServers(Environment $environment, OutputInterface $output, \Closure $action)
    {
        //Loop on the servers
        foreach ($environment->getServers() as $server) {
            $output->writeln("Deploying on <info>{$server->getHost()}</info>");
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed ?
            if (!$password = $server->getPassword()) {
                $text = "Password for <info>{$server->getUsername()}@{$server->getHost()}</info>:";
                $password = $this->getHelper('dialog')->askHiddenResponse($output, $text, false);
            }

            //Login to server
            $ssh = new Net_SFTP($server->getHost());
            if (!$ssh->login($server->getUsername(), $password)) {
                $output->writeln('<error>Login failed</error>');
                exit(self::EXIT_CODE_SERVER_LOGIN_FAILED);
            }

            $action($ssh);

            $ssh->disconnect();
        }
    }
}
