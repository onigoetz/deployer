<?php

namespace Deployer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deployer\Extensions\phpseclib\Net\SFTP;
use Deployer\Actions;

class RollbackCommand extends Command 
{
    protected function configure()
    {
        $this
            ->setName('rollback')
            ->setDescription('Rollback to the release before')
            ->addArgument('env', InputArgument::REQUIRED, 'The environment to rollback')
            ->addOption('verbose', 'v')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Registry::get('config');
        
        $env = $input->getArgument('env');
        //$input->getOption('yell')
        //$output->writeln($text);$parser->addOption('verbose', array(

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

        //Prepares the snapshot name
        $config_deploy['directories']['snapshot'] = strftime($config_deploy['directories']['snaphsot_pattern']);

        $output->writeln('This snapshot\'s name will be : ' . $config_deploy['directories']['snapshot']);

        //Loop on the servers
        foreach($config_servers as $server){
            $output->writeln('Deploying on server '.$server['host']);
            $output->writeln('-------------------------------------------------');

            //Ask server password if needed ?
            if(!array_key_exists('password', $server)){
                $server['password'] = ask_password('Server Password');
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
            $output->writeln('Previous snapshot : '.$previous);
            var_dump($previous);

            $output->writeln('Reverting...'.LN, 'blue');
            $output->writeln('<info>'.Actions::rmfile($directories['deploy']).'</info>');
            $output->writeln('<info>'.Actions::symlink($previous,$directories['deploy']).'</info>');
            $output->writeln('Done'.LN);
        } else {
            $output->writeln('<error>Cannot find previous file !!!</error>');
        }
        
        $output->writeln('Done');
        
        
    }
}



