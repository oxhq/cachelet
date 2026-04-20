<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Builders\CacheletBuilder;
use Oxhq\Cachelet\Facades\Cachelet;

it('generates stable keys for reordered payloads', function () {
    $first = Cachelet::for('users')->from(['b' => 2, 'a' => 1]);
    $second = Cachelet::for('users')->from(['a' => 1, 'b' => 2]);

    expect($first->key())->toBe($second->key());
});

it('uses the configured default ttl when none is provided', function () {
    $builder = Cachelet::for('users')->from(['id' => 1]);

    expect($builder->duration())->toBe(3600)
        ->and($builder->coordinate()->ttl)->toBe(3600);
});

it('parses integer, string, and datetime ttls', function () {
    $datetime = Carbon::now()->addMinutes(5);

    expect(Cachelet::for('int')->from('x')->ttl(120)->duration())->toBe(120)
        ->and(Cachelet::for('str')->from('x')->ttl('+2 hours')->duration())->toBe(7200)
        ->and(Cachelet::for('dt')->from('x')->ttl($datetime)->duration())->toBe(300);
});

it('stores and fetches cached values', function () {
    $builder = Cachelet::for('remember')->from(['id' => 10])->ttl(60);

    $first = $builder->remember(fn () => 'computed');
    $second = $builder->fetch();

    expect($first)->toBe('computed')
        ->and($second)->toBe('computed');

    expectCachelet($builder->key())
        ->toBeStored()
        ->toHaveValue('computed');
});

it('can store forever and invalidate a single key', function () {
    $builder = Cachelet::for('forever')->from(['id' => 11]);

    $builder->rememberForever(fn () => 'persisted');

    expect($builder->coordinate()->ttl)->toBeNull();
    expect(Cache::has($builder->key()))->toBeTrue();

    $builder->invalidate();

    expect(Cache::has($builder->key()))->toBeFalse();
});

it('resolves the facade to the canonical builder', function () {
    $builder = Cachelet::for('users');

    expect($builder)->toBeInstanceOf(CacheletBuilder::class);
});
