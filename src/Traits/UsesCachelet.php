<?php

namespace Garaekz\Cachelet\Traits;

use Garaekz\Cachelet\Observers\CacheletModelObserver;
use Garaekz\Cachelet\Support\Cachelet;

trait UsesCachelet
{
    public static function bootUsesCachelet(): void
    {
        static::observe(CacheletModelObserver::class);
    }

    public function cachelet(): \Garaekz\Cachelet\Core\CacheletBuilder
    {
        return Cachelet::forModel($this);
    }
}
