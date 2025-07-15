<?php

namespace Garaekz\Cachelet\Strategies;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Illuminate\Support\Facades\Cache;

class RegistryInvalidationStrategy
{
    public function invalidate(CacheletDefinition $definition): void
    {
        $prefix = $definition->prefix ?? 'generic';
        $setKey = "cachelet:registry:$prefix";
        $keys = Cache::getStore()->smembers($setKey);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::getStore()->del($setKey);
    }

    public function register(string $prefix, string $key): void
    {
        $setKey = "cachelet:registry:$prefix";
        Cache::getStore()->sadd($setKey, [$key]);
    }
}
