<?php

declare(strict_types=1);

use Garaekz\Cachelet\Facades\Cachelet;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('lists, inspects, and flushes keys by prefix', function () {
    $builder = Cachelet::for('commanded')->from(['id' => 7])->ttl(60);
    $builder->remember(fn () => 'value');

    $this->artisan('cachelet:list commanded')
        ->expectsOutput($builder->key())
        ->assertSuccessful();

    $this->artisan('cachelet:inspect commanded')
        ->expectsOutputToContain($builder->key())
        ->assertSuccessful();

    $this->artisan('cachelet:flush commanded')
        ->expectsOutput("Deleted: {$builder->key()}")
        ->assertSuccessful();

    expect(Cache::has($builder->key()))->toBeFalse();
});
