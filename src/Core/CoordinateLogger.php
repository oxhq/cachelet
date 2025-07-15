<?php

namespace Garaekz\Cachelet\Support;

use Garaekz\Cachelet\ValueObjects\CacheCoordinate;
use Illuminate\Support\Facades\Cache;

class CoordinateLogger
{
    public function log(CacheCoordinate $coordinate): void
    {
        $metaKey = "cachelet:meta:$coordinate->key";
        Cache::put($metaKey, $coordinate->toArray(), $coordinate->ttl);
    }

    public function inspect(string $prefix): array
    {
        $registry = Cache::getStore()->smembers("cachelet:registry:$prefix");

        return collect($registry)->map(function ($key) {
            return Cache::get("cachelet:meta:$key");
        })->filter()->all();
    }
}
