<?php

return [
    'observe' => [
        'auto_register' => true,
    ],
    'observability' => [
        'events' => [
            'enabled' => false,
        ],
    ],
    'dirty' => [
        'columns' => [
            'enabled' => true,
        ],
    ],,
    'defaults' => [
        'base' => '1hour',
        'prefix' => null,
    ],
    'observe' => [
        'auto_register' => true,
    ],
    'stale' => [
        'grace_suffix' => ':grace-lock',
        'grace_ttl' => 30,
    ],
    'observability' => [
        'events' => [
            'enabled' => false,
        ],
    ],
];