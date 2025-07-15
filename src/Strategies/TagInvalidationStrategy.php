<?php

namespace Garaekz\Cachelet\Strategies;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Illuminate\Support\Facades\Cache;

class TagInvalidationStrategy
{
    public function invalidate(CacheletDefinition $definition): void
    {
        if (!empty($definition->tags)) {
            $store = Cache::store();

            if (!method_exists($store, 'tags')) {
                return;
            }

            $store->tags($definition->tags)->flush();
        }
    }
}
