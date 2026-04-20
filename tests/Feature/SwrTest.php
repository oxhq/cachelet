<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;

beforeEach(function () {
    config(['cachelet.stale.refresh' => 'sync']);
    Cache::flush();
});

it('returns stale data and refreshes it in sync mode', function () {
    $builder = Cachelet::for('swr')->from(['id' => 1])->ttl(60);

    $builder->remember(fn () => 'old');

    $served = $builder->staleWhileRevalidate(fn () => 'fresh');

    expect($served)->toBe('old')
        ->and(Cache::get($builder->key()))->toBe('fresh');
});

it('uses the fallback on a miss while still computing and storing a fresh value', function () {
    $builder = Cachelet::for('swr-fallback')->from(['id' => 2])->ttl(60);

    $served = $builder->staleWhileRevalidate(
        fn () => 'fresh',
        fn () => 'fallback'
    );

    expect($served)->toBe('fallback')
        ->and(Cache::get($builder->key()))->toBe('fresh');
});

it('does not recompute while a refresh lock is already held', function () {
    $builder = Cachelet::for('swr-locked')->from(['id' => 3])->ttl(60);
    $builder->remember(fn () => 'old');

    Cache::put($builder->key().':refresh', true, 30);

    $runs = 0;
    $served = $builder->staleWhileRevalidate(function () use (&$runs) {
        $runs++;

        return 'fresh';
    });

    expect($served)->toBe('old')
        ->and($runs)->toBe(0)
        ->and(Cache::get($builder->key()))->toBe('old');
});
