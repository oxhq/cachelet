<?php

namespace Garaekz\Cachelet;

use Garaekz\Cachelet\Console\Commands\CacheletFlushCommand;
use Garaekz\Cachelet\Console\Commands\CacheletInspectCommand;
use Garaekz\Cachelet\Console\Commands\CacheletListCommand;
use Garaekz\Cachelet\Support\CoordinateLogger;
use Illuminate\Support\ServiceProvider;

class CacheletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cachelet.php', 'cachelet');

        $this->app->singleton(CacheletManager::class, function ($app) {
            return new CacheletManager((array) $app['config']->get('cachelet', []));
        });

        $this->app->alias(CacheletManager::class, 'cachelet');
        $this->app->singleton(CoordinateLogger::class, fn () => new CoordinateLogger);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cachelet.php' => config_path('cachelet.php'),
        ], 'cachelet-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheletListCommand::class,
                CacheletInspectCommand::class,
                CacheletFlushCommand::class,
            ]);
        }
    }
}
