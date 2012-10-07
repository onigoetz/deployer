<?php
namespace Deployer;

class Actions {
    
    static $replacement_dirs;
    
    /**
     * The ssh tunnel
     * 
     * @var Deployer\Extensions\phpseclib\Net\SFTP 
     */
    static $ssh;
    
    /**
     * The output stream
     * 
     * @var Symfony\Component\Console\Output\OutputInterface 
     */
    static $output;
    
    static function set_directories($directories){
        
        $dirs = array();
        foreach($directories as $key => $dir){
            $dirs['${'.$key.'}'] = $dir;
        }
        
        self::$replacement_dirs = $dirs;
    }
    
    static function _prepare_dir($dir){
        
        $dir = strtr($dir, self::$replacement_dirs);
        
        return str_replace(' ', '\ ',prepare_directory($dir, self::$replacement_dirs['${base_dir}']));
    }
    
    static function run_actions($actions){
        
        self::$ssh = Registry::get('ssh');
        self::$output = Registry::get('output');
        
        foreach($actions as $action){
            if(array_key_exists('description', $action)){
                self::$output->writeln($action['description']);
                unset($action['description']);
            }
            
            $action_name = current($action);
            $parameters =array_slice($action, 1);
            
            $parameters = array_map(array(__CLASS__, '_prepare_dir'), $parameters);
            
            $response = call_user_func_array(array(__CLASS__, $action_name), $parameters);
            self::$output->writeln('<server>' . $response);
        }
    }
    
    static function action($dir){
        
        $command = 'rm -Rf "'.$dir.'"';
        if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
        return self::$ssh->exec($command);
    }
    
    static function symlink($target, $link_name){
        $command = 'ln -s '.$target.' '.$link_name;
        if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
        
        return self::$ssh->exec($command);
    }
    
    static function rmfile($file){
        $command = 'rm -f "'.$file.'"';
        if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
        
        return self::$ssh->exec($command);
    }
    
    static function rmdir($file){
        $command = 'rm -rf "'.$file.'"';
        if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
        
        return self::$ssh->exec($command);
    }
    
    static function composer($dir){
        
        $composer_command = str_replace(LN, "", self::$ssh->exec('which composer'));
        
        //does composer exist ?
        if($composer_command != ''){
            
            $command = $composer_command . ' install';
            if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
            return self::$ssh->exec($command);
        } else {
            
            $command = 'curl -s https://getcomposer.org/installer | php -- --install-dir="'.$dir.'"';
            if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
            $response = self::$ssh->exec($command);
            
            self::$output->write('<server>' . $response);
            
            $command = 'cd ' . $dir . ' && ./composer.phar install';
            if(VERBOSE){self::$output->writeln('<command>  -> ' . $command . '</command>');}
            return self::$ssh->exec($command);
        }
    }
    
    //TODO :: finish pruning
    static function prune(){
        
    }
}
