<?php

return [
    'prod' => [
        'source' => 'cloned',
        'servers' => ['www1.youmewine.ch'],
        'overrides' => [
            'source' => ['branch' => 'master'],
        ],
        'tasks' => ['before' => ['before_actions']]
    ],
    'dev' => [
        'source' => 'cloned_dev',
        'servers' => ['www1.youmewine.ch'],
        'overrides' => [
            'source' => ['branch' => 'develop'],
            'directories' => ['root' => '/home/youmewine/domains/dev.youmewine.com/www/'],
        ],
        'tasks' => ['before' => ['before_actions'], 'after' => ['after_actions']]
    ],
];
