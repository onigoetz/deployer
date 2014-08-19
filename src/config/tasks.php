<?php

return array(
    //What to do before the deploy
    'before_actions' => array(
        'Link the images' => array(
            'action' => 'symlink',
            'target' => 'resources/images',
            'link_name' => '{{binary}}/public/images'
        ),
        'Compose' => array(
            'action' => 'composer',
            'dir' => '{{binary}}'
        )
    ),

    //What to do after the deploy
    'after_actions' => array(
        array(
            'action' => 'prune',
            'min_folders_left' => 1,
            'delete_older_than' => 3600 * 24 * 30 // 30 days
        )
    )
);
