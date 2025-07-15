<?php

namespace Garaekz\Cachelet\Core;

use Illuminate\Support\Facades\Cache;

class CacheletBuilder
{

    public function __construct(array|string|object $base)
    {
        $this->context = is_array($base) ? $base : ['base' => $base];
    
    


public function describe(string $text): self
    {
        $this->metadata['describe'] = $text;
        return $this;
    
    public function invalidateOn(string $cycle): self
    {
        $ttl = match ($cycle) {
            'daily' => now()->endOfDay()->diffInSeconds(),
            'weekly' => now()->endOfWeek()->diffInSeconds(),
            'monthly' => now()->endOfMonth()->diffInSeconds(),
            default => throw new \InvalidArgumentException("Unknown cycle: $cycle"),
        
    public function shouldStore(): self
    {
        if (!app()->runningUnitTests()) return $this;
        if (!$this->key) throw new \LogicException("Key not yet defined");
        if (!\Cache::has($this->key)) throw new \Exception("Expected cache to exist: {$this->key}");
        return $this;
    }

    public function shouldExpireIn(int $seconds): self
    {
        if (!app()->runningUnitTests()) return $this;
        $ttl = \Cache::getRedis()->ttl($this->key);
        if (abs($ttl - $seconds) > 5) {
            throw new \Exception("Expected TTL around {$seconds}, got {$ttl}");
        }
        return $this;
    }

    public function expectToThrow(string $class): void
    {
        try {
            $this->remember(fn () => throw new $class("Test throw"));
        } catch (\Throwable $e) {
            if (!is_a($e, $class)) {
                throw new \Exception("Expected exception {$class}, got " . get_class($e));
            }
            return;
        }
        throw new \Exception("No exception thrown, expected {$class}");
    }
    
}


public function remember(Closure $callback): mixed
    {
        // TODO: Build CacheletDefinition and pass to executor
        $definition = new CacheletDefinition(
            context: $this->context,
            prefix: $this->prefix,
            ttl: $this->ttl,
            tags: $this->tags,
            metadata: $this->metadata,
            resolver: $callback,
            staleCompute: $this->staleCompute,
            staleFallback: $this->staleFallback,
            graceTtl: $this->graceTtl
        );

        return app(CacheletExecutorInterface::class)->execute($definition);
    
    public function describe(string $text): self
    {
        $this->metadata['describe'] = $text;
        return $this;
    
    public function invalidateOn(string $cycle): self
    {
        $ttl = match ($cycle) {
            'daily' => now()->endOfDay()->diffInSeconds(),
            'weekly' => now()->endOfWeek()->diffInSeconds(),
            'monthly' => now()->endOfMonth()->diffInSeconds(),
            default => throw new \InvalidArgumentException("Unknown cycle: $cycle"),
        
    public function shouldStore(): self
    {
        if (!app()->runningUnitTests()) return $this;
        if (!$this->key) throw new \LogicException("Key not yet defined");
        if (!\Cache::has($this->key)) throw new \Exception("Expected cache to exist: {$this->key}");
        return $this;
    }

    public function shouldExpireIn(int $seconds): self
    {
        if (!app()->runningUnitTests()) return $this;
        $ttl = \Cache::getRedis()->ttl($this->key);
        if (abs($ttl - $seconds) > 5) {
            throw new \Exception("Expected TTL around {$seconds}, got {$ttl}");
        }
        return $this;
    }

    public function expectToThrow(string $class): void
    {
        try {
            $this->remember(fn () => throw new $class("Test throw"));
        } catch (\Throwable $e) {
            if (!is_a($e, $class)) {
                throw new \Exception("Expected exception {$class}, got " . get_class($e));
            }
            return;
        }
        throw new \Exception("No exception thrown, expected {$class}");
    }
    
}
