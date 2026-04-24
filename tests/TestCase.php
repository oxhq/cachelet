<?php

declare(strict_types=1);

namespace Oxhq\Cachelet\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Oxhq\Cachelet\CacheletModelServiceProvider;
use Oxhq\Cachelet\CacheletServiceProvider;
use Oxhq\Cachelet\Exporter\CacheletExporterServiceProvider;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Query\Facades\CacheletQuery;
use Oxhq\Cachelet\Query\Providers\CacheletQueryServiceProvider;
use Oxhq\Cachelet\Request\Facades\CacheletRequest;
use Oxhq\Cachelet\Request\Providers\CacheletRequestServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CacheletServiceProvider::class,
            CacheletModelServiceProvider::class,
            CacheletQueryServiceProvider::class,
            CacheletRequestServiceProvider::class,
            CacheletExporterServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Cachelet' => Cachelet::class,
            'CacheletQuery' => CacheletQuery::class,
            'CacheletRequest' => CacheletRequest::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.notags', [
            'driver' => 'notags',
        ]);
        $app['config']->set('cachelet.defaults.ttl', 3600);
        $app['config']->set('cachelet.defaults.prefix', 'cachelet');
        $app['config']->set('cachelet.observability.events.enabled', false);
        $app['config']->set('cachelet.stale.lock_ttl', 30);
        $app['config']->set('cachelet.stale.grace_ttl', 300);
        $app['config']->set('cachelet.locks.fill_ttl', 30);
        $app['config']->set('cachelet.locks.fill_wait', 5);
        $app['config']->set('cachelet.registry.store', null);
        $app['config']->set('cachelet.registry.prefix', 'cachelet:registry');
        $app['config']->set('cachelet.registry.metadata_ttl', null);
        $app['config']->set('cachelet.registry.lock_ttl', 10);
        $app['config']->set('cachelet.registry.lock_wait', 5);
        $app['config']->set('cachelet.telemetry.store', null);
        $app['config']->set('cachelet.telemetry.prefix', 'cachelet:telemetry');
        $app['config']->set('cachelet.telemetry.per_scope_limit', 100);
        $app['config']->set('cachelet.telemetry.retention', 86400);
        $app['config']->set('cachelet.serialization.exclude_dates', true);
        $app['config']->set('cachelet.serialization.default_excludes', []);
        $app['config']->set('cachelet.serialization.default_only', []);
        $app['config']->set('cachelet.query.default_prefix', 'query');
        $app['config']->set('cachelet.request.default_prefix', 'request');
        $app['config']->set('cachelet.request.middleware_alias', 'cachelet');
        $app['config']->set('cachelet-exporter.enabled', false);
        $app['config']->set('cachelet-exporter.transport', 'null');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Carbon::setTestNow(Carbon::create(2025, 7, 10, 12, 0, 0));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $cachePath = storage_path('framework/cache/data');
        (new Filesystem)->ensureDirectoryExists($cachePath);

        Schema::create('dummies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('role')->nullable();
            $table->timestamps();
            $table->timestamp('email_verified_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('cache', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
