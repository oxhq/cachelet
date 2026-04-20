<?php

declare(strict_types=1);

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Oxhq\Cachelet\Builders\CacheletBuilder;
use Oxhq\Cachelet\Events\CacheletTelemetryRecorded;

beforeEach(function () {
    config(['cachelet.observability.events.enabled' => true]);
    Cache::store('array')->flush();
    Cache::store('file')->flush();
    Event::fake([CacheletTelemetryRecorded::class]);
});

it('projects a canonical coordinate shape for cloud consumers', function () {
    $builder = Cachelet::for('users.index')
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

    $builder = new class('users.index', config('cachelet')) extends CacheletBuilder
    {
        protected function resolveRepository(): Repository
        {
            return Cache::store('file');
        }
    };

    $builder->from(['page' => 1])->remember(fn () => ['id' => 1]);

    expect($builder->coordinate()->store)->toBe('file');

    Event::assertDispatched(CacheletTelemetryRecorded::class, function (CacheletTelemetryRecorded $event) {
        return $event->record->event === 'stored'
            && $event->record->coordinate->store === 'file'
            && $event->record->toArray()['store'] === 'file';
    });
});
