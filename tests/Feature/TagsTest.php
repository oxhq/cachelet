<?php

declare(strict_types=1);

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Tests\Support\NoTagStore;

beforeEach(function () {
    Cache::flush();
});

it('stores values through tagged caches when the store supports tags', function () {
    $builder = Cachelet::for('tagless')
        ->from(['id' => 99])
        ->withTags(['users', 'api'])
        ->ttl(60);

    $value = $builder->remember(fn () => 'value');

    expect($value)->toBe('value')
        ->and(Cache::tags(['users', 'api'])->get($builder->key()))->toBe('value')
        ->and($builder->coordinate()->tags)->toBe(['users', 'api']);
});

it('gracefully ignores tags on stores without tag support', function () {
    app('cache')->extend('notags', fn () => new Repository(new NoTagStore));
    config(['cache.default' => 'notags']);
    app('cache')->forgetDriver('notags');

    $builder = Cachelet::for('notags')
        ->from(['id' => 42])
        ->withTags(['users', 'api'])
        ->ttl(60);

    $value = $builder->remember(fn () => 'fallback');

    expect($value)->toBe('fallback')
        ->and(Cache::get($builder->key()))->toBe('fallback')
        ->and($builder->coordinate()->tags)->toBe(['users', 'api']);
});
