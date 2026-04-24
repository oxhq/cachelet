<?php

declare(strict_types=1);

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Query\Support\QueryCacheletManager;
use Oxhq\Cachelet\ValueObjects\CacheScope;
use Tests\Models\Dummy;

beforeEach(function () {
    Cache::flush();

    Dummy::query()->create(['name' => 'Tom', 'role' => 'admin']);
    Dummy::query()->create(['name' => 'Sarah', 'role' => 'staff']);
    Dummy::query()->create(['name' => 'Ava', 'role' => 'admin']);
});

it('builds stable keys from sql bindings and connection data', function () {
    $first = Dummy::query()->where('role', 'admin')->cachelet();
    $second = Dummy::query()->where('role', 'admin')->cachelet();
    $third = Dummy::query()->where('role', 'staff')->cachelet();

    expect($first->key())->toBe($second->key())
        ->and($first->coordinate()->module)->toBe('query')
        ->and($first->coordinate()->metadata['type'])->toBe('query')
        ->and($third->key())->not->toBe($first->key());
});

it('prefers an explicit scope over the inferred query scope', function () {
    $projection = Dummy::query()
        ->where('role', 'admin')
        ->cachelet()
        ->scope(CacheScope::named('catalog.users'))
        ->coordinate()
        ->toProjection();

    expect($projection['scope'])->toMatchArray([
        'contract' => 'cachelet.scope.v1',
        'identifier' => 'catalog.users',
        'source' => 'explicit',
    ]);
});

it('infers a stable scope from the query table grouping', function () {
    $first = Dummy::query()->where('role', 'admin')->cachelet()->coordinate()->toProjection();
    $second = Dummy::query()->where('role', 'staff')->cachelet()->coordinate()->toProjection();

    expect($first['scope']['source'])->toBe('inferred')
        ->and($second['scope']['source'])->toBe('inferred')
        ->and($first['scope']['identifier'])->not->toBe('')
        ->and($first['scope']['identifier'])->toBe($second['scope']['identifier']);
});

it('caches query results and invalidates them by table prefix', function () {
    $query = Dummy::query()->where('role', 'admin')->cachelet();
    $store = Cache::store();
    $tags = $query->coordinate()->tags;

    $result = $query->rememberQuery();

    expect($result)->toHaveCount(2)
        ->and(
            $store->getStore() instanceof TaggableStore
                ? $store->tags($tags)->has($query->key())
                : Cache::has($query->key())
        )->toBeTrue();

    $deleted = app(QueryCacheletManager::class)->invalidateTable('dummies');

    expect($deleted)->toContain($query->key())
        ->and(
            $store->getStore() instanceof TaggableStore
                ? $store->tags($tags)->has($query->key())
                : Cache::has($query->key())
        )->toBeFalse();
});

it('includes pagination request inputs in the generated payload', function () {
    request()->query->set('page', 1);
    $pageOne = Dummy::query()->orderBy('id')->cachelet()->key();

    request()->query->set('page', 2);
    $pageTwo = Dummy::query()->orderBy('id')->cachelet()->key();

    expect($pageOne)->not->toBe($pageTwo);
});

it('supports the rememberWithCachelet macro on eloquent builders', function () {
    $results = Dummy::query()->where('role', 'admin')->rememberWithCachelet();

    expect($results)->toHaveCount(2);
});
