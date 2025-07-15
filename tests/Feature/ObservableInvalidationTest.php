<?php

declare(strict_types=1);

use Garaekz\Cachelet\Events\CacheletInvalidated;
use Garaekz\Cachelet\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\Models\Dummy;

uses(TestCase::class);

beforeEach(function () {
    Cache::flush();
    Event::fake();
});

/**
 * Helper to create & cache a model, returning the cache key.
 */
function cacheDummy(Dummy $m): string
{
    $builder = $m->cachelet()->ttl(60);
    $builder->fetch(fn () => 'value');

    return $builder->key();
}

it('invalidates cache on model update', function () {
    $m = Dummy::create(['name' => 'Tom']);
    $key = cacheDummy($m);

    expect(Cache::has($key))->toBeTrue();

    $m->name = 'Tommy';
    $m->save();

    expect(Cache::has($key))->toBeFalse();
});

it('dispatches CacheletInvalidated event on delete', function () {
    $m = Dummy::create(['name' => 'Jerry']);
    cacheDummy($m);

    $m->delete();

    Event::assertDispatched(CacheletInvalidated::class, function ($e) use ($m) {
        return $e->model->is($m)
            && $e->event === 'deleted'
            && is_string($e->key);
    });
});
