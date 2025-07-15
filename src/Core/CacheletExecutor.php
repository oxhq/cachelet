<?php

namespace Garaekz\Cachelet\Support;

use Garaekz\Cachelet\Contracts\CacheletExecutorInterface;
use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Garaekz\Cachelet\Core\CoordinateLogger;
use Garaekz\Cachelet\Strategies\RegistryInvalidationStrategy;
use Garaekz\Cachelet\Core\KeyHasher;
use Garaekz\Cachelet\Core\TtlParser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class CacheletExecutor implements CacheletExecutorInterface
{
    public function __construct(
        protected KeyHasher $hasher,
        protected TtlParser $ttlParser,
    ) {}

    public function handle(CacheletDefinition $definition): mixed
    {
        $key = $this->hasher->make($definition);
        $ttl = $this->ttlParser->parse(
            $definition->ttl ?? config('cachelet.defaults.base') ?? config('cachelet.defaults.fallback', 3600)
        );

        $store = Cache::store();

        if (!empty($definition->tags)) {
            if (!method_exists($store, 'tags')) {
                throw new \RuntimeException('This cache driver does not support tagging.');
            }

            $store = $store->tags($definition->tags);
        }

        // Handle staleWhileRevalidate logic
        if ($definition->staleCompute) {
            $value = $store->get($key);

            if ($value !== null) {
                $lockKey = $key . config('cachelet.stale.grace_suffix', ':grace-lock');
                $graceTtl = $definition->graceTtl ?? config('cachelet.stale.grace_ttl', 30);

                // If no other process is refreshing, acquire lock and refresh in background
                if (Cache::add($lockKey, 1, $graceTtl)) {
                    dispatch(function () use ($store, $key, $ttl, $definition) {
                        $fresh = call_user_func($definition->staleCompute);
                        $store->put($key, $fresh, $ttl);
                        Cache::forget($key . config('cachelet.stale.grace_suffix', ':grace-lock'));
                    });
                }

                return $value; // serve stale immediately
            }

            // No cached value, use fallback or compute synchronously
            if ($definition->staleFallback) {
                return call_user_func($definition->staleFallback);
            }

            return call_user_func($definition->staleCompute);
        }

        // Normal cache path
        $result = $store->remember($key, $ttl, $definition->resolver);

        if (config('cachelet.observability.events.enabled', false)) {
            Event::dispatch('cachelet.resolved', [
                'key' => $key,
                'tags' => $definition->tags,
                'metadata' => $definition->metadata,
                'ttl' => $ttl,
            ]);
        }

        return $result;
    }
}
