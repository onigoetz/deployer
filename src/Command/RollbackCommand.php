<?php

namespace Onigoetz\Deployer\Command;

use Onigoetz\Deployer\Actions;
use Onigoetz\Deployer\Extensions\phpseclib\Net\SFTP;
use Onigoetz\Deployer\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:rollback')
            ->setDescription('Rollback to the release before')
            ->addArgument('from', InputArgument::REQUIRED, 'The environment to rollback');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!defined('VERBOSE')) {
            define('VERBOSE', $input->getOption('verbose'));
        }

        Registry::set('output', $output);
        $config = Registry::get('config');
        $env = $input->getArgument('from');

        //Prepare configurations
        if (array_key_exists($env, $config['environments'])) {

            if (array_key_exists('deploy', $config['environments'][$env])) {
                $config_deploy = array_replace_recursive(
                    $config['deploy'],
                    $config['environments'][$env]['deploy']
                );
            } else {
                $config_deploy = $config['deploy'];
            }

            Registry::set('config_deploy', $config_deploy);

            $config_servers = $config['environments'][$env]['servers'];

        } else {
            $output->writeln('<error>Environnement not available</error>');
            exit(ERROR_ENVIRONMENT_NOT_AVAILABLE);
        }

        //Loop on the servers
        foreach ($config_servers as $server) {
            $output->writeln("Rollback on <info>{$server['host']}</info>");
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed
            if (!array_key_exists('password', $server)) {
                $dialog = $this->getHelperSet()->get('dialog');
                $text = "Password for <info>{$server['username']}@{$server['host']}</info>:";
                $server['password'] = $dialog->askHiddenResponse($output, $text, false);
            }

            //Login to server
            $ssh = new SFTP($server['host']);
            if (!$ssh->login($server['username'], $server['password'])) {
                $output->writeln('<error>Login failed</error>');
                exit(ERROR_SERVER_LOGIN_FAILED);
            }
            Registry::set('ssh', $ssh);

            $this->rollback($output);

            $ssh->disconnect();
        }
    }

    protected function rollback(OutputInterface $output)
    {
        $ssh = Registry::get('ssh');
        $config_deploy = Registry::get('config_deploy');

        $directories = array(
            'base_dir' => $config_deploy['directories']['base_dir'],
            'snapshots' => prepare_directory(
                $config_deploy['directories']['snapshots'],
                $config_deploy['directories']['base_dir']
            ),
            'deploy' => prepare_directory(
                $config_deploy['directories']['deploy'],
                $config_deploy['directories']['base_dir']
            )
        );

        Actions::setDirectories($directories);

        $previous = trim($ssh->exec('cat ' . $directories['snapshots'] . '/previous'));
        if ($previous != '') {
            $actions = array(
                array(
                    'description' => 'Removing the symlink of the release to rollback',
                    'action' => 'rmfile',
                    'target' => $directories['deploy']
                ),
                array(
                    'description' => 'Link it again to the snapshot ' . $previous,
                    'action' => 'symlink',
                    'target' => $previous,
                    'link_name' => $directories['deploy']
                )
            );

            $output->writeln('Previous snapshot : ' . $previous);
            $output->writeln("Reverting...\n", 'blue');
            Actions::runActions($actions);
            $output->writeln("Done\n");
        } else {
            $output->writeln('<error>Cannot find previous file !!!</error>');
        }
    }
}
