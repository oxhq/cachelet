<?php

return [
    'defaults' => [
        'ttl' => 3600,
        'prefix' => 'cachelet',
    ],
    'observability' => [
        'events' => [
            'enabled' => false,
        ],
    ],
    'observe' => [
        'auto_register' => true,
    ],
    'stale' => [
        'lock_suffix' => ':refresh',
        'lock_ttl' => 30,
        'refresh' => 'queue',
    ],
    'serialization' => [
        'exclude_dates' => true,
        'default_excludes' => [],
        'default_only' => [],
    ],
];
