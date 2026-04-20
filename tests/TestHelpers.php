<?php

use Oxhq\Cachelet\Testing\ExpectCachelet;

function expectCachelet(string $key): ExpectCachelet
{
    return new ExpectCachelet($key);
}

function redisTestConfig(): array
{
    return [
        'client' => 'phpredis',
        'default' => [
            'host' => env('CACHELET_REDIS_HOST', '127.0.0.1'),
            'password' => env('CACHELET_REDIS_PASSWORD', null),
            'port' => (int) env('CACHELET_REDIS_PORT', 6379),
            'database' => (int) env('CACHELET_REDIS_DB', 2),
        ],
        'cache' => [
            'host' => env('CACHELET_REDIS_HOST', '127.0.0.1'),
            'password' => env('CACHELET_REDIS_PASSWORD', null),
            'port' => (int) env('CACHELET_REDIS_PORT', 6379),
            'database' => (int) env('CACHELET_REDIS_DB', 2),
        ],
    ];
}

function pgsqlTestConfig(): array
{
    return [
        'driver' => 'pgsql',
        'host' => env('CACHELET_PGSQL_HOST', '127.0.0.1'),
        'port' => (int) env('CACHELET_PGSQL_PORT', 5432),
        'database' => env('CACHELET_PGSQL_DATABASE', 'postgres'),
        'username' => env('CACHELET_PGSQL_USERNAME', 'root'),
        'password' => env('CACHELET_PGSQL_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ];
}
