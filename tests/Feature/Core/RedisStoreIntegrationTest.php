<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Oxhq\Cachelet\Facades\Cachelet;

beforeEach(function () {
    config(['database.redis' => redisTestConfig()]);

    try {
        Redis::connection('cache')->ping();
    } catch (Throwable) {
        $this->markTestSkipped('Redis is not reachable for integration testing.');
    }

    config([
        'cache.default' => 'redis',
        'cache.stores.redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'cache',
        ],
    ]);

    app('cache')->forgetDriver('redis');
    Cache::store('redis')->flush();
});

it('supports tags and prefix invalidation on Redis', function () {
    $first = Cachelet::for('redis-users')
        ->from(['id' => 1])
        ->withTags(['users', 'api'])
        ->ttl(60);

    $second = Cachelet::for('redis-users')
        ->from(['id' => 2])
        ->withTags(['users', 'api'])
        ->ttl(60);

    $first->remember(fn () => 'one');
    $second->remember(fn () => 'two');

    expect(Cache::store('redis')->tags(['users', 'api'])->has($first->key()))->toBeTrue()
        ->and(Cache::store('redis')->tags(['users', 'api'])->has($second->key()))->toBeTrue();

    $deleted = $first->invalidatePrefix();

    expect($deleted)->toContain($first->key(), $second->key())
        ->and(Cache::store('redis')->tags(['users', 'api'])->has($first->key()))->toBeFalse()
        ->and(Cache::store('redis')->tags(['users', 'api'])->has($second->key()))->toBeFalse();
});
