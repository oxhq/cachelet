<?php

declare(strict_types=1);

namespace Oxhq\Cachelet\Cloud\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Oxhq\Cachelet\CacheletServiceProvider;
use Oxhq\Cachelet\Cloud\CacheletCloudServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CacheletServiceProvider::class,
            CacheletCloudServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.name', 'Cachelet Testbench');
        $app['config']->set('app.env', 'testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cachelet.defaults.ttl', 3600);
        $app['config']->set('cachelet.defaults.prefix', 'cachelet');
        $app['config']->set('cachelet.observability.events.enabled', true);
        $app['config']->set('cachelet.stale.lock_ttl', 30);
        $app['config']->set('cachelet.stale.grace_ttl', 300);
        $app['config']->set('cachelet.locks.fill_ttl', 30);
        $app['config']->set('cachelet.locks.fill_wait', 5);
        $app['config']->set('cachelet-cloud.enabled', true);
        $app['config']->set('cachelet-cloud.transport', 'null');
    }
}
