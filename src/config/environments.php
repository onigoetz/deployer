<?php

return array(
    'prod' => array(
        'source' => 'cloned',
        'servers' => array('www1.youmewine.ch'),
        'overrides' => array(
            'source' => array('branch' => 'master'),
        )
    ),
    'dev' => array(
        'source' => 'cloned_dev',
        'servers' => array('www1.youmewine.ch'),
        'overrides' => array(
            'source' => array('branch' => 'develop'),
            'directories' => array('base_dir' => '/home/youmewine/domains/dev.youmewine.com/www/'),
        )
    ),
);
