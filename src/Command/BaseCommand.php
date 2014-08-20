<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Environment;
use Onigoetz\Deployer\MethodCaller;
use Onigoetz\Deployer\RemoteActionRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    protected $manager;

    public function __construct(ConfigurationManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function prepareDir($dir, array $directories)
    {
        $dir = strtr($dir, $directories);

        if (strpos($dir, '/') !== 0) {
            $dir = $directories['{{root}}'] . '/' . $dir;
        }

        return $dir;
    }

    protected function runActions(RemoteActionRunner $runner, $actions, OutputInterface $output, $directories)
    {
        foreach ($actions as $description => $action) {
            $output->writeln($description);
            $this->runAction($runner, $action, $output, $directories);
        }
    }

    protected function runAction(RemoteActionRunner $runner, $action, OutputInterface $output, $directories)
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
        /**
         * @var $server \Onigoetz\Deployer\Configuration\Server
         */
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
                throw new \Exception("Login failed on host '{$server->getHost()}'");
            }

            $action($ssh);

            $ssh->disconnect();
        }
    }

    /**
     * Get a valid environment form string
     *
     * @param $env
     * @param OutputInterface $output
     * @return Environment
     * @throws \Exception
     */
    protected function getEnvironment($env, OutputInterface $output)
    {
        try {
            /**
             * @var Environment
             */
            $environment = $this->manager->get('environment', $env);
        } catch (\LogicException $e) {
            throw new \Exception("Environment '$env' doesn't exist");
        }

        if (!$environment->isValid()) {
            foreach ($this->manager->getLogs() as $line) {
                $output->writeln("<error>$line</error>");
            }
            throw new \Exception("Invalid configuration for '$env'");
        }

        return $environment;
    }
}
