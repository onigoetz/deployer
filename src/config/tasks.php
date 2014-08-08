<?php

return array(
    //What to do before the deploy
    'actions_before' => array(
        array(
            'description' => 'Link the images',
            'action' => 'symlink',
            'target' => 'resources/images',
            'link_name' => '{{snapshot}}/public/images'
        ),
        array(
            'description' => 'Compose',
            'action' => 'composer',
            'dir' => '{{snapshot}}'
        )
    ),

    //What to do after the deploy
    'actions_after' => array(
        array(
            'action' => 'prune',
            'min_folders_left' => 1,
            'delete_older_than' => 3600 * 24 * 30 // 30 days
        )
    )
);
