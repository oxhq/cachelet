<?php

namespace Garaekz\Cachelet\Testing;

use Illuminate\Support\Facades\Cache;

class ExpectCachelet
{
    public function __construct(
        public string $key
    ) {}

    public function toBeStored(): static
    {
        if (! Cache::has($this->key)) {
            throw new \Exception("Cachelet not stored: {$this->key}");
        }

        return $this;
    }

    public function toExpireAfter(int $seconds): static
    {
        $ttl = Cache::getRedis()->ttl($this->key);
        if (abs($ttl - $seconds) > 5) {
            throw new \Exception("Expected TTL ~$seconds, got $ttl");
        }

        return $this;
    }

    public function toHaveTags(array $tags): static
    {
        // For advanced drivers only
        return $this;
    }
}
