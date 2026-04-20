<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;

beforeEach(function () {
    $cachePath = storage_path('framework/cache/data');

    config([
        'cache.default' => 'file',
        'cache.stores.file' => [
            'driver' => 'file',
            'path' => $cachePath,
            'lock_path' => $cachePath,
        ],
    ]);

    app('cache')->forgetDriver('file');
    Cache::store('file')->flush();
});

it('supports null values and prefix invalidation on the file cache store', function () {
    $first = Cachelet::for('files')->from(['id' => 1])->ttl(60);
    $second = Cachelet::for('files')->from(['id' => 2])->ttl(60);

    $first->remember(fn () => null);
    $second->remember(fn () => 'value');

    expect($first->fetch())->toBeNull()
        ->and(Cache::store('file')->has($second->key()))->toBeTrue();

    $deleted = $first->invalidatePrefix();
    $recomputes = 0;
    $reloaded = $first->fetch(function () use (&$recomputes) {
        $recomputes++;

        return 'reloaded';
    });

    expect($deleted)->toContain($first->key(), $second->key())
        ->and($reloaded)->toBe('reloaded')
        ->and($recomputes)->toBe(1)
        ->and(Cache::store('file')->has($second->key()))->toBeFalse();
});
