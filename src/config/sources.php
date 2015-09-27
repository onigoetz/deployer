<?php

return [
    'cloned' => [
        // 'clone' or 'upload'
        'strategy' => 'clone',

        // strategy = upload -> '.' by default
        // strategy = clone -> you can either provide the full repository URL, or an array with options (example below)
        'path' => 'git@github.com:onigoetz/deployer.git',

        //In this case, the username is created on the fly
        //'path' => [
        //    'repository' => 'https://github.com/onigoetz/deployer.git',
        //    'username' => 'yourUsername',
        //	  'password' => 'yourPassword' //optional, if not provided, it will ask for it at deployment time
        //)

        // Clone Specific

        // Only git available for the moment
        'type' => 'git',

        // the branch to use
        'branch' => 'master',

        // Do you want submodules with it ?
        'submodules' => true,
    ],
    'uploaded' => [
        'strategy' => 'upload',

        'path' => dirname(__DIR__),

        //paths to include with the build, takes everything by default
        'include' => [
            'app',
            'public',
        ],

        //paths to exclude
        'exclude' => [
            'app/storage',
        ],
    ],
    'cloned_dev' => [

        //you can choose to extend an existing configuration
        'extends' => 'cloned',

        // the branch to use
        'branch' => 'submodule',
    ],
];
