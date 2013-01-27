<?php

namespace Deployer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Deployer\Registry;
use Deployer\Extensions\phpseclib\Net\SFTP;
use Deployer\Actions;

class DeployCommand extends Command 
{
    protected function configure()
    {
        $this
            ->setName('server:deploy')
            ->setDescription('Deploy the latest release')
            ->addArgument('env', InputArgument::REQUIRED, 'The environment to deploy')
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
            
            //Prepares the snapshot name
            $config_deploy['directories']['snapshot'] = strftime($config_deploy['directories']['snapshot_pattern']);

            $output->writeln('This snapshot\'s name will be : ' . $config_deploy['directories']['snapshot']);
            
            Registry::set('config_deploy', $config_deploy);

            $config_servers = $config['environments'][$env]['servers'];

        } else {
            $output->writeln('<error>Environnement not available</error>');
            exit(ERROR_ENVIRONMENT_NOT_AVAILABLE);
        }

        //Loop on the servers
        foreach($config_servers as $server){
            $output->writeln('Deploying on server '.$server['host']);
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed ?
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
        $config = Registry::get('config');
        $config_deploy = Registry::get('config_deploy');
        
        $directories = array(
            'base_dir' => $config_deploy['directories']['base_dir'],
            'snapshots' => prepare_directory($config_deploy['directories']['snapshots'], $config_deploy['directories']['base_dir']),
            'deploy' => prepare_directory($config_deploy['directories']['deploy'], $config_deploy['directories']['base_dir'])
        );

        $directories['snapshot'] = $directories['snapshots'] . '/' .$config_deploy['directories']['snapshot'];

        /*
        * Do the directories Exists ? 
        */
        $output->writeln('Does the snapshots directory exist ?', 'blue');

        if(!$ssh->directory_exists($directories['snapshots'])){
            $output->writeln('<info> CREATING </info>');

            $command = 'mkdir -p "'.$directories['snapshots'].'"';
            if(VERBOSE) { $output->writeln('  -> '.$command); }
            $ssh->exec($command);

            if(!$ssh->directory_exists($directories['snapshots'])){
                $output->writeln('<error>Cannot create directory "'.$directories['snapshots'].'"</error>');
                exit(ERROR_CANNOT_CREATE_DIRECTORY_SNAPSHOTS);
            }
        }
        $output->writeln('<info> OK </info>');

        /*
        * SCM Section 
        */
        $class = 'Deployer\\SCM\\'.ucfirst($config_deploy['scm']['type']);
        if(!class_exists($class)){
            $output->writeln('<error> Cannot find SCM: '.$class.' </error>');
        }
        
        $scm = new $class();
        
        
        $output->writeln('<info>Cloning ...</info>');
        if(!array_key_exists('final_url', $config_deploy['scm'])){
            $config_deploy['scm']['final_url'] = $scm->final_url();
        }
        
        $command = $scm->clone_command($directories);
        if(VERBOSE){ $output->writeln('<bg=blue;options=bold>  -> ' . $command . '</bg=blue;options=bold>');}

        $output->writeln('<info>'.$ssh->exec($command).'</info>');

        /*
        * Before Deploy Section 
        */
        $output->writeln('Before deploy actions');
        include(dirname(__FILE__).'/../actions.php');

        Actions::set_directories($directories);

        //Before deploy
        if(array_key_exists('actions_before', $config_deploy)){
            Actions::run_actions($config_deploy['actions_before']);
        }

        $ln = str_replace("\n", '', $ssh->exec('ls -la '.$directories['deploy']));


        //Store "previous" deploy
        $previous = trim(substr($ln, strpos($ln, '->')+3));

        if($previous != ''){
            $output->writeln('Previous snapshot : '.$previous);
            $ssh->put($directories['snapshots'].'/previous', $previous);
        }

        //Symlink the folder
        $output->writeln('Deploy');
        $output->writeln('<info>'.Actions::rmfile($directories['deploy']).'</info>');
        $output->writeln('<info>'.Actions::symlink($directories['snapshot'],$directories['deploy']).'</info>');

        //After deploy
        $output->writeln('After deploy actions');
        if(array_key_exists('actions_after', $config_deploy)){
            Actions::run_actions($config_deploy['actions_after']);
        }

        $output->writeln('Done');
    }
}



