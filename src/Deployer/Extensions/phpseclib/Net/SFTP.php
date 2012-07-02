<?php

namespace Deployer\Extensions\phpseclib\Net;

class SFTP extends \Net_SFTP {

    /**
     * Tests if a directory exists
     * @param type $directory 
     */
    function directory_exists($dir){
        
        $pwd = $this->pwd();
        
        if(!$this->chdir($dir)){
            return false;
        }
        
        $this->chdir($pwd);
        
        return true;
    }
    
}
