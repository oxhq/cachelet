<?php

namespace Garaekz\Cachelet;

use Illuminate\Support\ServiceProvider;

class CacheletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cachelet.php', 'cachelet');

        $this->app->singleton('cachelet', function ($app) {
            return new CacheletManager($app['config']['cachelet']);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cachelet.php' => config_path('cachelet.php'),
        ], 'cachelet-config');

        $this->registerMacros();
    }

    protected function registerMacros(): void
    {
        // Aquí puedes registrar macros globales si lo necesitas
    }
}
