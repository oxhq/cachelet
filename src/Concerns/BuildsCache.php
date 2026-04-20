<?php

namespace Oxhq\Cachelet\Concerns;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Oxhq\Cachelet\Events\CacheletHit;
use Oxhq\Cachelet\Events\CacheletInvalidated;
use Oxhq\Cachelet\Events\CacheletMiss;
use Oxhq\Cachelet\Events\CacheletStored;
use Oxhq\Cachelet\Support\CoordinateLogger;

trait BuildsCache
{
    public function fetch(?Closure $callback = null): mixed
    {
        $value = $this->getStoredValue();

        if ($value !== null) {
            $this->dispatchCacheEvent('hit', $this->key(), $value);

            return $value;
        }

        $this->dispatchCacheEvent('miss', $this->key());

        if ($callback === null) {
            return null;
        }

        return $this->computeAndStore($callback);
    }

    public function staleWhileRevalidate(Closure $callback, ?Closure $fallback = null): mixed
    {
        $cached = $this->getStoredValue();

        if ($cached !== null) {
            $this->dispatchCacheEvent('hit', $this->key(), $cached);
            $this->backgroundRefresh($callback);

            return $cached;
        }

        $this->dispatchCacheEvent('miss', $this->key());

        if ($fallback !== null) {
            $this->backgroundRefresh($callback);

            return value($fallback);
        }

        return $this->computeAndStore($callback);
    }

    protected function backgroundRefresh(Closure $callback): void
    {
        $lockKey = $this->key().($this->config['stale']['lock_suffix'] ?? ':refresh');
        $lockTtl = (int) ($this->config['stale']['lock_ttl'] ?? 30);

        if (! Cache::add($lockKey, true, $lockTtl)) {
            return;
        }

        $refresh = function () use ($callback, $lockKey): void {
            try {
                $this->computeAndStore($callback);
            } finally {
                Cache::forget($lockKey);
            }
        };

        match ($this->config['stale']['refresh'] ?? 'queue') {
            'sync' => $refresh(),
            'defer' => app()->terminating($refresh),
            default => dispatch($refresh)->afterResponse(),
        };
    }

    protected function computeAndStore(Closure $callback): mixed
    {
        $value = value($callback);

        $this->putStoredValue($value);
        $this->coordinateLogger()->record($this->coordinate());
        $this->dispatchCacheEvent('stored', $this->key(), $value);

        return $value;
    }

    protected function getStoredValue(): mixed
    {
        return $this->resolveStore()->get($this->key());
    }

    protected function putStoredValue(mixed $value): void
    {
        $store = $this->resolveStore();
        $ttl = $this->duration();

        if ($ttl === null) {
            $store->forever($this->key(), $value);

            return;
        }

        $store->put($this->key(), $value, $ttl);
    }

    protected function resolveStore(): Repository|TaggedCache
    {
        $store = Cache::store();

        if ($this->tags === [] || ! $this->supportsTags($store)) {
            return $store;
        }

        return $store->tags($this->tags);
    }

    protected function supportsTags(Repository $store): bool
    {
        return $store->getStore() instanceof TaggableStore;
    }

    protected function dispatchCacheEvent(string $type, string $key, mixed $value = null): void
    {
        if (! ($this->config['observability']['events']['enabled'] ?? false)) {
            return;
        }

        $event = match ($type) {
            'hit' => new CacheletHit($key, $value),
            'miss' => new CacheletMiss($key),
            'stored' => new CacheletStored($key, $value),
            default => throw new InvalidArgumentException("Unknown cache event type [{$type}]."),
        };

        event($event);
    }

    protected function dispatchInvalidatedEvent(array $keys, string $reason = 'manual'): void
    {
        if (! ($this->config['observability']['events']['enabled'] ?? false)) {
            return;
        }

        event($this->makeInvalidatedEvent($keys, $reason));
    }

    protected function makeInvalidatedEvent(array $keys, string $reason): CacheletInvalidated
    {
        return new CacheletInvalidated(
            prefix: $this->prefix,
            keys: array_values($keys),
            reason: $reason
        );
    }

    protected function coordinateLogger(): CoordinateLogger
    {
        return app(CoordinateLogger::class);
    }
}
