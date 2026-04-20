<?php

namespace Oxhq\Cachelet\Testing;

use Illuminate\Support\Facades\Cache;

class ExpectCachelet
{
    public function __construct(
        public string $key
    ) {}

    public function toBeStored(): static
    {
        if (! Cache::has($this->key)) {
            throw new \RuntimeException("Cachelet not stored: {$this->key}");
        }

        return $this;
    }

    public function toHaveValue(mixed $expected): static
    {
        if (Cache::get($this->key) !== $expected) {
            throw new \RuntimeException("Cachelet value mismatch for {$this->key}");
        }

        return $this;
    }
}
