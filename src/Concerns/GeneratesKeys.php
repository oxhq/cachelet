<?php

namespace Garaekz\Cachelet\Concerns;

use Garaekz\Cachelet\Support\PayloadNormalizer;

trait GeneratesKeys
{
    protected ?string $computedKey = null;

    protected ?PayloadNormalizer $normalizer = null;

    public function key(): string
    {
        return $this->computedKey ??= $this->generateKey();
    }

    protected function generateKey(): string
    {
        $parts = [
            $this->prefix,
            $this->version,
            $this->hashPayload($this->normalizedPayload()),
        ];

        return implode(':', array_filter($parts, fn ($v) => $v !== null));
    }

    protected function normalizedPayload(): mixed
    {
        $this->normalizer ??= new PayloadNormalizer($this->options);

        return $this->normalizer->normalize($this->payload);
    }

    protected function hashPayload(mixed $payload): string
    {
        return md5(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function resetComputedValues(): void
    {
        $this->computedKey = null;
        $this->normalizer = null;
    }
}
