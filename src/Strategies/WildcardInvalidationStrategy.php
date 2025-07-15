<?php

namespace Garaekz\Cachelet\Strategies;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Contracts\Cache\Store;

class WildcardInvalidationStrategy
{
    public function invalidate(CacheletDefinition $definition): void
    {
        $store = Cache::store();

        if (!method_exists($store, 'getRedis')) {
            return;
        }

        $redis = $store->getRedis();
        $prefix = config('cache.prefix') . ':' ?? '';
        $pattern = $prefix . '*';

        $cursor = null;
        do {
            [$cursor, $keys] = $redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 100]);
            if ($keys) {
                $redis->del(...$keys);
            }
        } while ($cursor > 0);
    }
}
