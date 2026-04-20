<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;

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

it('publishes the package config', function () {
    $target = config_path('cachelet.php');

    if (file_exists($target)) {
        unlink($target);
    }

    $this->artisan('vendor:publish --tag=cachelet-config --force')
        ->assertSuccessful();

    expect(file_exists($target))->toBeTrue();
});
