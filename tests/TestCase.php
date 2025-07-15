<?php

declare(strict_types=1);

namespace Garaekz\Cachelet\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [\Garaekz\Cachelet\CacheletServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Cachelet' => \Garaekz\Cachelet\Facades\Cachelet::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Cache config
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cachelet.defaults', ['base' => '1 hour']);
        $app['config']->set('cachelet.observability.events.enabled', true);

        // DB in-memory SQLite
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Freeze time
        Carbon::setTestNow(Carbon::create(2025, 7, 10, 12, 0, 0));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create the `dummies` table for our Dummy model
        Schema::create('dummies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
            $table->timestamp('email_verified_at')->nullable();
        });
    }
}
