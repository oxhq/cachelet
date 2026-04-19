<?php

namespace Garaekz\Cachelet\Traits;

use Garaekz\Cachelet\Builders\ModelCacheletBuilder;
use Garaekz\Cachelet\Facades\Cachelet;
use Garaekz\Cachelet\Observers\CacheletModelObserver;

trait UsesCachelet
{
    public static function bootUsesCachelet(): void
    {
        if (config('cachelet.observe.auto_register', true)) {
            static::observe(CacheletModelObserver::class);
        }
    }

    public function cachelet(): ModelCacheletBuilder
    {
        return Cachelet::forModel($this);
    }
}
