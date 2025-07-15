<?php

namespace Garaekz\Cachelet\ValueObjects;

use Closure;

readonly class CacheletDefinition
{
    public function __construct(
        public array $context,
        public ?string $prefix,
        public string|int|null $ttl,
        public array $tags,
        public array $metadata,
        public Closure $resolver,
        public ?Closure $staleCompute = null,
        public ?Closure $staleFallback = null,
        public ?int $graceTtl = null,
    ) {}

    public function keyTokens(): array
    {
        return array_filter([
            'prefix' => $this->prefix,
            ...$this->context,
        ], fn ($v) => $v !== null);
    }

    public function toArray(): array
    {
        return [
            'context' => $this->context,
            'prefix' => $this->prefix,
            'ttl' => $this->ttl,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
        ];
    }
}
