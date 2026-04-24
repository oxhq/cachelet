<?php

return [
    'enabled' => env('CACHELET_EXPORTER_ENABLED', false),
    'transport' => env('CACHELET_EXPORTER_TRANSPORT', 'null'),
    'throw' => env('CACHELET_EXPORTER_THROW', false),
    'client' => [
        'endpoint' => env('CACHELET_EXPORTER_ENDPOINT'),
        'token' => env('CACHELET_EXPORTER_TOKEN'),
        'timeout' => (int) env('CACHELET_EXPORTER_TIMEOUT', 3),
        'headers' => [],
    ],
    'log' => [
        'channel' => env('CACHELET_EXPORTER_LOG_CHANNEL'),
    ],
    'source' => [
        'app' => env('CACHELET_EXPORTER_APP', env('APP_NAME', 'Laravel')),
        'environment' => env('CACHELET_EXPORTER_ENVIRONMENT', env('APP_ENV', 'production')),
        'instance' => env('CACHELET_EXPORTER_INSTANCE'),
    ],
];
