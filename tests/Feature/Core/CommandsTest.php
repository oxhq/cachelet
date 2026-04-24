<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Support\TelemetryStore;
use Oxhq\Cachelet\ValueObjects\CacheScope;
use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

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

it('prunes orphaned registry entries and expired telemetry sidecars', function () {
    config()->set('cachelet.telemetry.retention', 60);

    $scope = CacheScope::named('ops.prune');
    $builder = Cachelet::for('commanded')
        ->from(['id' => 9])
        ->scope($scope)
        ->ttl(60);

    $builder->remember(fn () => 'value');
    Cache::forget($builder->key());

    app(TelemetryStore::class)->record(new CacheTelemetryRecord(
        event: 'hit',
        coordinate: $builder->coordinate(),
        context: ['entry_state' => 'fresh'],
        occurredAt: Carbon::now()->subMinutes(5)->toIso8601String(),
    ));

    $this->artisan('cachelet:prune')
        ->expectsOutputToContain('Registry: scanned')
        ->expectsOutputToContain('Telemetry: scanned')
        ->assertSuccessful();

    $this->artisan('cachelet:list commanded')
        ->doesntExpectOutput($builder->key())
        ->assertSuccessful();

    expect(Cache::has($builder->key()))->toBeFalse()
        ->and(app(TelemetryStore::class)->recordsForScopeSince($scope, Carbon::now()->subDay()->toIso8601String()))
        ->toBe([]);
});
