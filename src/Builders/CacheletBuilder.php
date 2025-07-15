<?php

namespace Garaekz\Cachelet\Builders;

use Closure;
use Garaekz\Cachelet\Concerns\BuildsCache;
use Garaekz\Cachelet\Concerns\GeneratesKeys;
use Garaekz\Cachelet\Concerns\HandlesTtl;
use Garaekz\Cachelet\Contracts\CacheletBuilderInterface;
use Garaekz\Cachelet\ValueObjects\CacheCoordinate;

class CacheletBuilder implements CacheletBuilderInterface
{
    use BuildsCache;
    use GeneratesKeys;
    use HandlesTtl;

    protected string $prefix;

    protected mixed $payload = null;

    protected array $config;

    protected array $metadata = [];

    protected array $tags = [];

    protected ?string $version = null;

    protected array $options = [
        'normalize' => true,
        'excludeTimestamps' => true,
    ];

    public function __construct(string $prefix, array $config = [])
    {
        $this->prefix = $prefix;
        $this->config = $config;
    }

    public function from(mixed $payload): static
    {
        $this->payload = $payload;
        $this->resetComputedValues();

        return $this;
    }

    public function withMetadata(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    public function withTags(string|array $tags): static
    {
        $tags = is_string($tags) ? [$tags] : $tags;
        $this->tags = array_unique(array_merge($this->tags, $tags));

        return $this;
    }

    public function versioned(?string $version = null): static
    {
        $this->version = $version ?? $this->config['version'] ?? 'v1';

        return $this;
    }

    public function only(array $fields): static
    {
        $this->options['only'] = $fields;

        return $this;
    }

    public function exclude(array $fields): static
    {
        $this->options['exclude'] = $fields;

        return $this;
    }

    public function remember(Closure $callback): mixed
    {
        return $this->fetch($callback);
    }

    public function rememberForever(Closure $callback): mixed
    {
        $this->ttl = null;

        return $this->fetch($callback);
    }

    public function coordinate(): CacheCoordinate
    {
        return new CacheCoordinate(
            key: $this->key(),
            ttl: $this->duration(),
            tags: $this->tags,
            metadata: $this->metadata
        );
    }
}
