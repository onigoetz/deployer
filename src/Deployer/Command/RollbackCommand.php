<?php

namespace Deployer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Deployer\Actions;
use Deployer\Registry;
use Deployer\Extensions\phpseclib\Net\SFTP;

class RollbackCommand extends Command 
{
    protected function configure()
    {
        $this
            ->setName('server:rollback')
            ->setDescription('Rollback to the release before')
            ->addArgument('env', InputArgument::REQUIRED, 'The environment to rollback')
            ->addOption('verbose', 'v')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!defined('VERBOSE')) {
            define('VERBOSE', $input->getOption('verbose'));
        }
        
        Registry::set('output', $output);
        $config = Registry::get('config');
        $env = $input->getArgument('env');

        //Prepare configurations
        if(array_key_exists($env ,$config['environments'])){

            if(array_key_exists('deploy', $config['environments'][$env])){
                $config_deploy  = array_merge_recursive_distinct($config['deploy'], $config['environments'][$env]['deploy']);
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
        foreach($config_servers as $server){
            $output->writeln('Rollback on server '.$server['host']);
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed
            if(!array_key_exists('password', $server)){
                $server['password'] = ask_password('Server Password');
                echo "\n";
            }

            //Login to server
            $ssh = new SFTP($server['host']);
            if (!$ssh->login($server['username'], $server['password'])) {
                $output->writeln('<error>Login failed</error>');
                exit(ERROR_SERVER_LOGIN_FAILED);
            }
            Registry::set('ssh', $ssh);
            
            $this->command_specific($output);

            $ssh->disconnect();
        }
    }
    
    function command_specific(OutputInterface $output){
        $ssh = Registry::get('ssh');
        $config_deploy = Registry::get('config_deploy');
        
        $directories = array(
            'base_dir' => $config_deploy['directories']['base_dir'],
            'snapshots' => prepare_directory($config_deploy['directories']['snapshots'], $config_deploy['directories']['base_dir']),
            'deploy' => prepare_directory($config_deploy['directories']['deploy'], $config_deploy['directories']['base_dir'])
        );

        Actions::set_directories($directories);

        $previous = trim($ssh->exec('cat '.$directories['snapshots'].'/previous'));
        if($previous != ''){
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
            
            $output->writeln('Previous snapshot : '.$previous);
            $output->writeln("Reverting...\n", 'blue');
            Actions::run_actions($actions);
            $output->writeln("Done\n");
        } else {
            $output->writeln('<error>Cannot find previous file !!!</error>');
        }
    }
}



