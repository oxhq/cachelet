<?php

namespace Garaekz\Cachelet\Concerns;

use Closure;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

trait HandlesTtl
{
    protected mixed $ttl = null;

    protected ?int $computedSeconds = null;

    public function ttl(null|int|string|\DateTimeInterface|Closure $ttl): static
    {
        $this->ttl = $ttl;
        $this->computedSeconds = null;

        return $this;
    }

    public function duration(): int
    {
        return $this->computedSeconds ??= $this->parseTtl($this->ttl);
    }

    public function expiresAt(): Carbon
    {
        return Carbon::now()->addSeconds($this->duration());
    }

    protected function parseTtl(mixed $ttl): int
    {
        return match (true) {
            $ttl instanceof Closure => $this->parseTtl(value($ttl)),
            $ttl === null => $this->parseTtl($this->getDefaultTtl()),
            is_int($ttl) => $this->validateSeconds($ttl),
            $ttl instanceof \DateTimeInterface => max(0, Carbon::now()->diffInSeconds($ttl)),
            is_string($ttl) => $this->parseStringTtl($ttl),
        };
    }

    protected function getDefaultTtl(): int|string
    {
        $defaults = $this->config['defaults'] ?? [];

        return $defaults[$this->prefix] ?? $defaults['base'] ?? 3600;
    }

    protected function validateSeconds(int $seconds): int
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException("TTL must be positive, got {$seconds}");
        }

        return $seconds;
    }

    protected function parseStringTtl(string $ttl): int
    {
        try {
            return max(0, Carbon::now()->diffInSeconds(Carbon::parse($ttl)));
        } catch (\Throwable) {
            throw new InvalidArgumentException("Invalid TTL string: {$ttl}");
        }
    }
}
