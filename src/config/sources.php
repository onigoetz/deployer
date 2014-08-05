<?php

return array(
    'cloned' => array(
        // 'clone' or 'upload'
        'strategy' => 'clone',

        // strategy = upload -> '.' by default
        // strategy = clone -> you can either provide the full repository URL, or an array with options (example below)
        'path' => 'git@github.com:onigoetz/deployer.git',

        //In this case, the username is created on the fly
        //'path' => array(
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
        'submodules' => true
    ),
    'uploaded' => array(
        'strategy' => 'upload',

        'path' => '.',

        //paths to include with the build, takes everything by default
        'include' => array(
            'app',
            'public'
        ),

        //paths to exclude
        'exclude' => array(
            'app/storage'
        )
    ),
    'cloned_dev' => array(

        //you can choose to extend an existing configuration
        'extends' => 'cloned',

        // the branch to use
        'branch' => 'submodule'
    ),
);
