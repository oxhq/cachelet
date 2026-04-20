<?php

return [
    'enabled' => env('CACHELET_CLOUD_ENABLED', false),
    'transport' => env('CACHELET_CLOUD_TRANSPORT', 'null'),
    'throw' => env('CACHELET_CLOUD_THROW', false),
    'client' => [
        'endpoint' => env('CACHELET_CLOUD_ENDPOINT'),
        'token' => env('CACHELET_CLOUD_TOKEN'),
        'timeout' => (int) env('CACHELET_CLOUD_TIMEOUT', 3),
        'headers' => [],
    ],
    'log' => [
        'channel' => env('CACHELET_CLOUD_LOG_CHANNEL'),
    ],
    'source' => [
        'app' => env('CACHELET_CLOUD_APP', env('APP_NAME', 'Laravel')),
        'environment' => env('CACHELET_CLOUD_ENVIRONMENT', env('APP_ENV', 'production')),
        'instance' => env('CACHELET_CLOUD_INSTANCE'),
    ],
];
