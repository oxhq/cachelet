<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;

beforeEach(function () {
    config([
        'cache.default' => 'database',
        'cache.stores.database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => 'sqlite',
            'lock_connection' => 'sqlite',
        ],
    ]);

    app('cache')->forgetDriver('database');
    Cache::store('database')->flush();
});

it('supports registry-backed invalidation on the database cache store', function () {
    $first = Cachelet::for('database-users')->from(['id' => 1])->ttl(60);
    $second = Cachelet::for('database-users')->from(['id' => 2])->ttl(60);

    $first->remember(fn () => 'one');
    $second->remember(fn () => 'two');

    $deleted = $first->invalidatePrefix();

    expect($deleted)->toContain($first->key(), $second->key())
        ->and(Cache::store('database')->has($first->key()))->toBeFalse()
        ->and(Cache::store('database')->has($second->key()))->toBeFalse();
});
