<?php

declare(strict_types=1);

namespace Oxhq\Cachelet\Tests\Support;

use Illuminate\Contracts\Cache\Store;

class NoTagStore implements Store
{
    protected array $values = [];

    public function get($key): mixed
    {
        if (! array_key_exists($key, $this->values)) {
            return null;
        }

        if ($this->isExpired($key)) {
            unset($this->values[$key]);

            return null;
        }

        return $this->values[$key]['value'];
    }

    public function many(array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }

        return $values;
    }

    public function put($key, $value, $seconds): bool
    {
        $this->values[$key] = [
            'value' => $value,
            'expires_at' => time() + max(1, (int) $seconds),
        ];

        return true;
    }

    public function putMany(array $values, $seconds): bool
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $seconds);
        }

        return true;
    }

    public function increment($key, $value = 1): int|bool
    {
        $current = (int) ($this->get($key) ?? 0);
        $next = $current + $value;
        $this->forever($key, $next);

        return $next;
    }

    public function decrement($key, $value = 1): int|bool
    {
        $current = (int) ($this->get($key) ?? 0);
        $next = $current - $value;
        $this->forever($key, $next);

        return $next;
    }

    public function forever($key, $value): bool
    {
        $this->values[$key] = [
            'value' => $value,
            'expires_at' => null,
        ];

        return true;
    }

    public function forget($key): bool
    {
        unset($this->values[$key]);

        return true;
    }

    public function flush(): bool
    {
        $this->values = [];

        return true;
    }

    public function getPrefix(): string
    {
        return '';
    }

    protected function isExpired(string $key): bool
    {
        $expiresAt = $this->values[$key]['expires_at'];

        return $expiresAt !== null && $expiresAt <= time();
    }
}
