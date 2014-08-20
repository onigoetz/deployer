<?php

return [
    //this folder will contain everything
    'root' => '/var/www/application/',

    // this folder contains all deployed versions
    // will be 'root' + 'binaries'
    'binaries' => 'binaries',

    // symlink name to the current version
    // will be 'root' + 'deploy'
    'deploy' => 'www',

    // the pattern to use to name the deployed binaries on the server
    // uses strftime : http://php.net/strftime
    'binary_name' => '%G-%m-%d_%H-%M',
];
