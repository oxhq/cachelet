<?php

namespace Garaekz\Cachelet\Observers;

use Illuminate\Database\Eloquent\Model;

class RelationObserver
{
    public static function register(): void
    {
        // Register pivot event listeners
        \Illuminate\Support\Facades\Event::listen('eloquent.pivotAttached*', fn() => null);
        \Illuminate\Support\Facades\Event::listen('eloquent.pivotDetached*', fn() => null);
        \Illuminate\Support\Facades\Event::listen('eloquent.pivotUpdated*', fn() => null);
    }
}
