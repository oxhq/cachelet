<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Oxhq\Cachelet\Events\CacheletTelemetryRecorded;
use Oxhq\Cachelet\Exporter\Contracts\ExporterClient;
use Oxhq\Cachelet\ValueObjects\CacheCoordinate;
use Oxhq\Cachelet\ValueObjects\CacheScope;
use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

beforeEach(function () {
    config(['cachelet.observability.events.enabled' => true]);
    Cache::store('array')->flush();
    Cache::store('file')->flush();
    Event::fake([CacheletTelemetryRecorded::class]);
});

it('projects a canonical coordinate shape for cloud consumers', function () {
    $builder = Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1])
        ->versioned('v2');

    expect($builder->coordinate()->toProjection())->toMatchArray([
        'contract' => 'cachelet.coordinate.v1',
        'module' => 'core',
        'prefix' => 'users.index',
        'ttl' => 3600,
        'version' => 'v2',
        'store' => 'array',
        'swr' => [
            'capable' => true,
            'configured' => true,
            'refresh' => 'sync',
            'grace_ttl' => 300,
            'refresh_lock_ttl' => 30,
            'fill_lock_ttl' => 30,
            'fill_wait' => 5,
        ],
        'metadata' => [
            'module' => 'core',
            'type' => 'core',
            'scope' => 'agency.users',
            'scope_source' => 'explicit',
        ],
        'scope' => [
            'contract' => 'cachelet.scope.v1',
            'identifier' => 'agency.users',
            'source' => 'explicit',
        ],
    ]);
});

it('emits canonical telemetry records for cache lifecycle events', function () {
    $builder = Cachelet::for('users.index')->from(['page' => 1]);

    $builder->remember(fn () => ['id' => 1]);
    $builder->fetch();

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->event === 'stored'
            && $event->record->coordinate->module === 'core'
            && $event->record->context['access_strategy'] === 'standard'
            && $event->record->context['swr_runtime']['requested'] === false
            && $event->record->context['value_type'] === 'array';
    });

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->event === 'hit'
            && $event->record->coordinate->module === 'core'
            && $event->record->context['entry_state'] === 'fresh'
            && $event->record->context['swr_runtime']['requested'] === false;
    });
});

it('distinguishes swr policy from runtime swr usage in telemetry', function () {
    $builder = Cachelet::for('users.index')->from(['page' => 1])->ttl(60);

    $builder->staleWhileRevalidate(fn () => ['id' => 1]);

    Carbon::setTestNow(Carbon::now()->addSeconds(61));

    $builder->staleWhileRevalidate(fn () => ['id' => 2]);

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->event === 'hit'
            && $event->record->context['access_strategy'] === 'stale_while_revalidate'
            && $event->record->context['swr_runtime'] === [
                'requested' => true,
                'background_refresh' => false,
                'served_stale' => true,
                'entry_state' => 'stale',
            ];
    });
});

it('projects the actual resolved store instead of the default driver', function () {
    config(['cache.default' => 'array']);
    $builder = Cachelet::for('users.index')
        ->onStore('file')
        ->from(['page' => 1]);

    $builder->remember(fn () => ['id' => 1]);

    expect($builder->coordinate()->store)->toBe('file');

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->event === 'stored'
            && $event->record->coordinate->store === 'file'
            && $event->record->toArray()['store'] === 'file';
    });
});

it('can keep registry and telemetry sidecars on a dedicated store', function () {
    config([
        'cachelet.registry.store' => 'file',
        'cachelet.telemetry.store' => 'file',
    ]);

    Cache::store('file')->flush();

    $builder = Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1]);

    $builder->remember(fn () => ['id' => 1]);
    Cache::store('array')->flush();

    expect(Cachelet::interventions()->forScope('agency.users')->preview()->matchedCoordinateCount)
        ->toBeGreaterThanOrEqual(1);

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->coordinate->scope?->identifier === 'agency.users';
    });
});

it('includes scope projections in telemetry records', function () {
    $builder = Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1]);

    $builder->remember(fn () => ['id' => 1]);

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->event === 'stored'
            && data_get($event->record->toArray(), 'coordinate.scope.identifier') === 'agency.users'
            && data_get($event->record->toArray(), 'coordinate.scope.source') === 'explicit';
    });
});

it('propagates scope through exporter payloads', function () {
    Http::fake();

    config([
        'cachelet-exporter.enabled' => true,
        'cachelet-exporter.transport' => 'http',
        'cachelet-exporter.client.endpoint' => 'https://cloud.example.test/ingest',
        'cachelet-exporter.client.token' => 'secret-token',
        'cachelet-exporter.source.instance' => 'worker-1',
    ]);

    $record = CacheTelemetryRecord::capture(
        'stored',
        CacheCoordinate::fromArray([
            'prefix' => 'users.index',
            'key' => 'cachelet:test-key',
            'ttl' => 300,
            'module' => 'core',
            'store' => 'array',
            'scope' => [
                'contract' => 'cachelet.scope.v1',
                'identifier' => 'agency.users',
                'source' => 'explicit',
            ],
        ]),
        ['access_strategy' => 'standard']
    );

    app(ExporterClient::class)->export($record);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://cloud.example.test/ingest'
            && $request->hasHeader('Authorization', 'Bearer secret-token')
            && $request['record']['coordinate']['scope']['identifier'] === 'agency.users'
            && $request['record']['coordinate']['scope']['source'] === 'explicit';
    });
});
