<?php

namespace Onigoetz\Deployer\Command;

use Net_SFTP;
use Onigoetz\Deployer\Configuration\ConfigurationManager;
use Onigoetz\Deployer\Configuration\Environment;
use Onigoetz\Deployer\MethodCaller;
use Onigoetz\Deployer\RemoteActionRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Command
{
    protected $manager;

    public function __construct(ConfigurationManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function prefixPath($path, array $directories)
    {
        if (strpos($path, '/') === 0) {
            return $path;
        }

        return $directories['{{root}}'] . '/' . $path;
    }

    protected function replaceVars($dir, array $directories)
    {
        return strtr($dir, $directories);
    }

    protected function parameterToCamelCase($key)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
    }

    protected function runActions(RemoteActionRunner $runner, $actions, OutputInterface $output, $directories)
    {
        foreach ($actions as $description => $action) {
            $this->runAction(
                $description,
                $output,
                function () use ($runner, $action, $directories) {
                    $method = $action['action'];
                    unset($action['action']);

                    $parameters = [];
                    foreach ($action as $key => $value) {
                        $key = $this->parameterToCamelCase($key);
                        $parameters[$key] = $this->replaceVars($value, $directories);

                        if ($method != 'exec') {
                            $parameters[$key] = $this->prefixPath($parameters[$key], $directories);
                        }
                    }

                    return (new MethodCaller)->call($runner, $method, $parameters);
                }
            );
        }
    }

    protected function runAction($title, OutputInterface $output, \Closure $closure)
    {
        $output->write($title);
        // 8 is the length of the label + 2 let it breathe
        $padding = $this->getApplication()->getTerminalDimensions()[0] - strlen($title) - 10;

        try {
            $response = $closure();
        } catch (\Exception $e) {
            $output->writeln(str_pad(' ', $padding) . '[ <fg=red>FAIL</fg=red> ]');
            throw $e;
        }

        $output->writeln(str_pad(' ', $padding) . '[  <fg=green>OK</fg=green>  ]');
        if (!empty($response)) {
            $output->writeln('<fg=blue>' . $response . '</fg=blue>');
        }
    }

    protected function allServers(
        Environment $environment,
        InputInterface $input,
        OutputInterface $output,
        \Closure $action
    ) {
        //Loop on the servers
        /**
         * @var \Onigoetz\Deployer\Configuration\Server
         */
        foreach ($environment->getServers() as $server) {
            $output->writeln("Deploying on <info>{$server->getHost()}</info>");
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed ?
            if (!$password = $server->getPassword()) {
                $text = "Password for <info>{$server->getUsername()}@{$server->getHost()}</info>:";
                $question = new Question($text, false);
                $question->setHidden(true)->setHiddenFallback(false);
                $password = $this->getHelper('question')->ask($input, $output, $question);
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
     * @throws \Exception
     * @return Environment
     */
    protected function getEnvironment($env, OutputInterface $output)
    {
        try {
            /*
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
