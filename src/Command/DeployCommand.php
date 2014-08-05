<?php

namespace Onigoetz\Deployer\Command;

use Onigoetz\Deployer\Actions;
use Onigoetz\Deployer\Extensions\phpseclib\Net\SFTP;
use Onigoetz\Deployer\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:deploy')
            ->setDescription('Deploy the latest release')
            ->addArgument('to', InputArgument::REQUIRED, 'The environment to deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!defined('VERBOSE')) {
            define('VERBOSE', $input->getOption('verbose'));
        }

        Registry::set('output', $output);
        $config = Registry::get('config');
        $env = $input->getArgument('to');

        //Prepare configurations
        if (!array_key_exists($env, $config['environments'])) {
            $output->writeln('<error>Environnement not available</error>');
            exit(ERROR_ENVIRONMENT_NOT_AVAILABLE);
        }

        if (array_key_exists('deploy', $config['environments'][$env])) {
            $config_deploy = array_replace_recursive($config['deploy'], $config['environments'][$env]['deploy']);
        } else {
            $config_deploy = $config['deploy'];
        }

        //Prepares the snapshot name
        $config_deploy['directories']['snapshot'] = strftime($config_deploy['directories']['snapshot_pattern']);

        $output->writeln('This snapshot\'s name will be : ' . $config_deploy['directories']['snapshot']);

        Registry::set('config_deploy', $config_deploy);

        $config_servers = $config['environments'][$env]['servers'];

        //Loop on the servers
        foreach ($config_servers as $server) {
            $output->writeln("Deploying on <info>{$server['host']}</info>");
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed ?
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

            $this->deploy($output);

            $ssh->disconnect();
        }
    }

    protected function deploy(OutputInterface $output)
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

        $directories['snapshot'] = $directories['snapshots'] . '/' . $config_deploy['directories']['snapshot'];

        /*
        * Do the directories Exists ?
        */
        $output->writeln('Does the snapshots directory exist ?', 'blue');

        if (!$ssh->directory_exists($directories['snapshots'])) {
            $output->writeln('<info> CREATING </info>');

            $command = 'mkdir -p "' . $directories['snapshots'] . '"';
            if (VERBOSE) {
                $output->writeln('  -> ' . $command);
            }
            $ssh->exec($command);

            if (!$ssh->directory_exists($directories['snapshots'])) {
                $output->writeln('<error>Cannot create directory "' . $directories['snapshots'] . '"</error>');
                exit(ERROR_CANNOT_CREATE_DIRECTORY_SNAPSHOTS);
            }
        }
        $output->writeln('<info> OK </info>');

        /*
        * SCM Section
        */
        $class = 'Deployer\\SCM\\' . ucfirst($config_deploy['scm']['type']);
        if (!class_exists($class)) {
            $output->writeln('<error> Cannot find SCM: ' . $class . ' </error>');
        }

        $scm = new $class();


        $output->writeln('<info>Cloning ...</info>');
        if (!array_key_exists('final_url', $config_deploy['scm'])) {
            $config_deploy['scm']['final_url'] = $scm->getFinalUrl();
        }

        $command = $scm->cloneCommand($directories);
        if (VERBOSE) {
            $output->writeln('<bg=blue;options=bold>  -> ' . $command . '</bg=blue;options=bold>');
        }

        $output->writeln('<info>' . $ssh->exec($command) . '</info>');

        /*
        * Before Deploy Section
        */
        $output->writeln('Before deploy actions');
        include(dirname(__FILE__) . '/../actions.php');

        Actions::setDirectories($directories);

        //Before deploy
        if (array_key_exists('actions_before', $config_deploy)) {
            Actions::runActions($config_deploy['actions_before']);
        }

        $ln = str_replace("\n", '', $ssh->exec('ls -la ' . $directories['deploy']));


        //Store "previous" deploy
        $previous = trim(substr($ln, strpos($ln, '->') + 3));

        if ($previous != '') {
            $output->writeln('Previous snapshot : ' . $previous);
            $ssh->put($directories['snapshots'] . '/previous', $previous);
        }

        //Symlink the folder
        $output->writeln('Deploy');
        $output->writeln('<info>' . Actions::rmfile($directories['deploy']) . '</info>');
        $output->writeln('<info>' . Actions::symlink($directories['snapshot'], $directories['deploy']) . '</info>');

        //After deploy
        $output->writeln('After deploy actions');
        if (array_key_exists('actions_after', $config_deploy)) {
            Actions::runActions($config_deploy['actions_after']);
        }

        $output->writeln('Done');
    }
}
