<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Oxhq\Cachelet\Facades\Cachelet;

beforeEach(function () {
    config(['database.connections.pgsql_cachelet' => pgsqlTestConfig()]);

    try {
        DB::connection('pgsql_cachelet')->getPdo();
    } catch (Throwable) {
        $this->markTestSkipped('PostgreSQL is not reachable for integration testing.');
    }

    if (Schema::connection('pgsql_cachelet')->hasTable('cachelet_cache')) {
        Schema::connection('pgsql_cachelet')->drop('cachelet_cache');
    }

    Schema::connection('pgsql_cachelet')->create('cachelet_cache', function ($table): void {
        $table->string('key')->primary();
        $table->text('value');
        $table->integer('expiration');
    });

    config([
        'cache.default' => 'database',
        'cache.stores.database' => [
            'driver' => 'database',
            'table' => 'cachelet_cache',
            'connection' => 'pgsql_cachelet',
            'lock_connection' => 'pgsql_cachelet',
        ],
    ]);

    app('cache')->forgetDriver('database');
    Cache::store('database')->flush();
});

afterEach(function () {
    try {
        if (Schema::connection('pgsql_cachelet')->hasTable('cachelet_cache')) {
            Schema::connection('pgsql_cachelet')->drop('cachelet_cache');
        }
    } catch (Throwable) {
        // Best-effort cleanup for external integration state.
    }
});

it('supports registry-backed invalidation on a PostgreSQL-backed database cache store', function () {
    $first = Cachelet::for('pgsql-users')->from(['id' => 1])->ttl(60);
    $second = Cachelet::for('pgsql-users')->from(['id' => 2])->ttl(60);

    $first->remember(fn () => 'one');
    $second->remember(fn () => 'two');

    $deleted = $first->invalidatePrefix();

    expect($deleted)->toContain($first->key(), $second->key())
        ->and(Cache::store('database')->has($first->key()))->toBeFalse()
        ->and(Cache::store('database')->has($second->key()))->toBeFalse();
});
