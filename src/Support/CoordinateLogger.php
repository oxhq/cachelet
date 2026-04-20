<?php

namespace Oxhq\Cachelet\Support;

use Illuminate\Cache\Repository;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\ValueObjects\CacheCoordinate;

class CoordinateLogger
{
    public function record(CacheCoordinate $coordinate): void
    {
        $keys = $this->keys($coordinate->prefix);

        if (! in_array($coordinate->key, $keys, true)) {
            $keys[] = $coordinate->key;
            Cache::forever($this->registryKey($coordinate->prefix), array_values($keys));
        }

        $this->storeMetadata($coordinate);
    }

    public function forget(CacheCoordinate $coordinate): void
    {
        $this->forgetStoredValue($coordinate);
        Cache::forget($this->metadataKey($coordinate->key));
        $this->removeFromRegistry($coordinate->prefix, $coordinate->key);
    }

    public function flush(string $prefix): array
    {
        $coordinates = $this->coordinatesForPrefix($prefix);
        $deleted = [];

        foreach ($coordinates as $coordinate) {
            $this->forgetStoredValue($coordinate);
            Cache::forget($this->metadataKey($coordinate->key));
            $deleted[] = $coordinate->key;
        }

        Cache::forget($this->registryKey($prefix));

        return $deleted;
    }

    public function inspect(string $prefix): array
    {
        return array_map(
            static fn (CacheCoordinate $coordinate): array => $coordinate->toArray(),
            $this->coordinatesForPrefix($prefix)
        );
    }

    public function keys(string $prefix): array
    {
        $keys = Cache::get($this->registryKey($prefix), []);

        if (! is_array($keys)) {
            return [];
        }

        return array_values(array_unique(array_filter($keys, 'is_string')));
    }

    protected function coordinatesForPrefix(string $prefix): array
    {
        $coordinates = [];

        foreach ($this->keys($prefix) as $key) {
            $metadata = Cache::get($this->metadataKey($key));

            if (! is_array($metadata) || ! isset($metadata['key'])) {
                $this->removeFromRegistry($prefix, $key);

                continue;
            }

            $coordinates[] = CacheCoordinate::fromArray($metadata);
        }

        return $coordinates;
    }

    protected function storeMetadata(CacheCoordinate $coordinate): void
    {
        $payload = $coordinate->toArray();

        if ($coordinate->ttl === null) {
            Cache::forever($this->metadataKey($coordinate->key), $payload);

            return;
        }

        Cache::put($this->metadataKey($coordinate->key), $payload, $coordinate->ttl);
    }

    protected function forgetStoredValue(CacheCoordinate $coordinate): void
    {
        $store = Cache::store();

        if ($coordinate->tags !== [] && $this->supportsTags($store)) {
            $store->tags($coordinate->tags)->forget($coordinate->key);

            return;
        }

        Cache::forget($coordinate->key);
    }

    protected function supportsTags(Repository $store): bool
    {
        return $store->getStore() instanceof TaggableStore;
    }

    protected function removeFromRegistry(string $prefix, string $key): void
    {
        $remaining = array_values(array_filter(
            $this->keys($prefix),
            static fn (string $registeredKey): bool => $registeredKey !== $key
        ));

        if ($remaining === []) {
            Cache::forget($this->registryKey($prefix));

            return;
        }

        Cache::forever($this->registryKey($prefix), $remaining);
    }

    protected function registryKey(string $prefix): string
    {
        return 'cachelet:registry:'.md5($prefix);
    }

    protected function metadataKey(string $key): string
    {
        return 'cachelet:meta:'.sha1($key);
    }
}
