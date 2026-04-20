<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;

beforeEach(function () {
    Cache::flush();
});

it('returns fresh values without refreshing while they are still within the fresh window', function () {
    $builder = Cachelet::for('swr')->from(['id' => 1])->ttl(60);

    $builder->staleWhileRevalidate(fn () => 'old');

    $runs = 0;
    $served = $builder->staleWhileRevalidate(function () use (&$runs) {
        $runs++;

        return 'fresh';
    });

    expect($served)->toBe('old')
        ->and($runs)->toBe(0)
        ->and($builder->fetch())->toBe('old');
});

it('returns stale data and refreshes it once the fresh window has elapsed', function () {
    config(['cachelet.stale.refresh' => 'sync']);

    $builder = Cachelet::for('swr')->from(['id' => 1])->ttl(60);

    $builder->staleWhileRevalidate(fn () => 'old');

    Carbon::setTestNow(Carbon::now()->addSeconds(61));

    $served = $builder->staleWhileRevalidate(fn () => 'fresh');

    expect($served)->toBe('old')
        ->and($builder->fetch())->toBe('fresh');
});

it('uses the fallback on a miss while still computing and storing a fresh value', function () {
    config(['cachelet.stale.refresh' => 'sync']);

    $builder = Cachelet::for('swr-fallback')->from(['id' => 2])->ttl(60);

    $served = $builder->staleWhileRevalidate(
        fn () => 'fresh',
        fn () => 'fallback'
    );

    expect($served)->toBe('fallback')
        ->and($builder->fetch())->toBe('fresh');
});

it('does not recompute while a refresh lock is already held', function () {
    config(['cachelet.stale.refresh' => 'sync']);

    $builder = Cachelet::for('swr-locked')->from(['id' => 3])->ttl(60);
    $builder->staleWhileRevalidate(fn () => 'old');

    Carbon::setTestNow(Carbon::now()->addSeconds(61));

    Cache::put($builder->key().':refresh', true, 30);

    $runs = 0;
    $served = $builder->staleWhileRevalidate(function () use (&$runs) {
        $runs++;

        return 'fresh';
    });

    expect($served)->toBe('old')
        ->and($runs)->toBe(0)
        ->and($builder->fetch())->toBe('old');
});

it('uses the shipped sync refresh mode by default', function () {
    $builder = Cachelet::for('swr-default')->from(['id' => 4])->ttl(60);
    $builder->staleWhileRevalidate(fn () => 'old');

    Carbon::setTestNow(Carbon::now()->addSeconds(61));

    $served = $builder->staleWhileRevalidate(fn () => 'fresh');

    expect(config('cachelet.stale.refresh'))->toBe('sync')
        ->and($served)->toBe('old')
        ->and($builder->fetch())->toBe('fresh');
});

it('falls back safely when queue refresh is configured in console execution', function () {
    config(['cachelet.stale.refresh' => 'queue']);

    $builder = Cachelet::for('swr-queue')->from(['id' => 5])->ttl(60);
    $builder->staleWhileRevalidate(fn () => 'old');

    Carbon::setTestNow(Carbon::now()->addSeconds(61));

    $served = $builder->staleWhileRevalidate(fn () => 'fresh');

    expect($served)->toBe('old')
        ->and($builder->fetch())->toBe('fresh');
});
