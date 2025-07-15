<?php

namespace Garaekz\Cachelet\Contracts;

use Closure;
use Garaekz\Cachelet\ValueObjects\CacheCoordinate;

interface CacheletBuilderInterface
{
    public function from(mixed $payload): static;

    public function ttl(mixed $ttl): static;

    public function withTags(string|array $tags): static;

    public function withMetadata(array $metadata): static;

    public function key(): string;

    public function duration(): int;

    public function fetch(?Closure $callback = null): mixed;

    public function remember(Closure $callback): mixed;

    public function invalidate(): void;

    public function coordinate(): CacheCoordinate;
}
