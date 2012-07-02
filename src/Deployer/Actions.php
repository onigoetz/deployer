<?php
namespace Deployer;

use Deployer\Registry;

class Actions {
    
    static $replacement_dirs;
    static $ssh;
    
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
        
        foreach($actions as $action){
            if(array_key_exists('description', $action)){
                out($action['description'].LN);
                unset($action['description']);
            }
            
            $action_name = current($action);
            $parameters =array_slice($action, 1);
            
            $parameters = array_map(array(__CLASS__, '_prepare_dir'), $parameters);
            
            call_user_func_array(array(__CLASS__, $action_name), $parameters);
        }
    }
    
    static function action($dir){
        
        $command = 'rm -Rf "'.$dir.'"';
        if(VERBOSE){out('  -> '.$command.LN);}        
        return self::$ssh->exec($command);
    }
    
    static function symlink($target, $link_name){
        
        $command = 'ln -s '.$target.' '.$link_name;
        if(VERBOSE){out('  -> '.$command.LN);}  
        
        return self::$ssh->exec($command);
    }
    
    static function rmfile($file){
        
        $command = 'rm -f "'.$file.'"';
        if(VERBOSE){out('  -> '.$command.LN);}   
        
        return self::$ssh->exec($command);
    }
    
    //TODO :: finish pruning
    static function prune(){
        
    }
}
