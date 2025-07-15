<?php

namespace Garaekz\Cachelet\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

trait BuildsCache
{
    public function fetch(?Closure $callback = null): mixed
    {
        $key = $this->key();
        $value = Cache::get($key);

        if ($value !== null || $callback === null) {
            $this->dispatchEvent('hit', $key, $value);

            return $value;
        }

        $this->dispatchEvent('miss', $key);

        $value = Cache::remember($key, $this->duration(), $callback);
        $this->dispatchEvent('stored', $key, $value);

        return $value;
    }

    public function staleWhileRevalidate(Closure $callback, ?Closure $fallback = null): mixed
    {
        $cached = Cache::get($this->key());

        if ($cached !== null) {
            $this->backgroundRefresh($callback);

            return $cached;
        }

        if ($fallback !== null) {
            $this->backgroundRefresh($callback);

            return value($fallback);
        }

        return $this->computeAndStore($callback);
    }

    protected function backgroundRefresh(Closure $callback): void
    {
        if (! Cache::add($this->key().':refresh', true, 30)) {
            return;
        }

        $task = fn () => $this->computeAndStore($callback);

        match ($this->config['stale']['driver'] ?? 'queue') {
            'defer' => app()->terminating($task),
            'sync' => $task(),
            default => dispatch($task)->afterResponse()
        };
    }

    protected function computeAndStore(Closure $callback): mixed
    {
        $value = value($callback);
        Cache::put($this->key(), $value, $this->duration());
        $this->dispatchEvent('stored', $this->key(), $value);

        return $value;
    }

    protected function dispatchEvent(string $type, string $key, mixed $value = null): void
    {
        if (! ($this->config['observability']['events']['enabled'] ?? false)) {
            return;
        }

        $eventClass = match ($type) {
            'hit' => \Garaekz\Cachelet\Events\CacheletHit::class,
            'miss' => \Garaekz\Cachelet\Events\CacheletMiss::class,
            'stored' => \Garaekz\Cachelet\Events\CacheletStored::class,
        };

        event(new $eventClass($key, $value));
    }
}
