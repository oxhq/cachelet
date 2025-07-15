<?php

namespace Garaekz\Cachelet\ValueObjects;

use Illuminate\Support\Carbon;

readonly class CacheCoordinate
{
    public function __construct(
        public string $key,
        public ?int $ttl,
        public array $tags = [],
        public array $metadata = []
    ) {}

    public function expiresAt(): ?Carbon
    {
        return $this->ttl ? Carbon::now()->addSeconds($this->ttl) : null;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'ttl' => $this->ttl,
            'expires_at' => $this->expiresAt()?->toIso8601String(),
            'tags' => $this->tags,
            'metadata' => $this->metadata,
        ];
    }
}
