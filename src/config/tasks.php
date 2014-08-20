<?php

return [
    //What to do before the deploy
    'before_actions' => [
        'Link the images' => [
            'action' => 'symlink',
            'target' => 'resources/images',
            'link_name' => '{{binary}}/public/images',
        ],
        'Compose' => [
            'action' => 'composer',
            'dir' => '{{binary}}',
        ],
    ],

    //What to do after the deploy
    'after_actions' => [
        [
            'action' => 'prune',
            'min_folders_left' => 1,
            'delete_older_than' => 3600 * 24 * 30, // 30 days
        ],
    ],
];
