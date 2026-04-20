<?php

declare(strict_types=1);

use Oxhq\Cachelet\CacheletManager;
use Oxhq\Cachelet\Contracts\PayloadValueNormalizer;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Support\PayloadNormalizer;

it('supports pluggable payload normalizers in core', function () {
    app(CacheletManager::class)->prependPayloadNormalizer(new class implements PayloadValueNormalizer
    {
        public function supports(mixed $value): bool
        {
            return $value instanceof DateTimeImmutable;
        }

        public function normalize(mixed $value, PayloadNormalizer $normalizer): mixed
        {
            return ['custom' => 'normalized'];
        }
    });

    $key = Cachelet::for('custom-normalizer')->from(new DateTimeImmutable('2026-01-01T00:00:00Z'))->key();

    expect($key)->toBe(Cachelet::for('custom-normalizer')->from(['custom' => 'normalized'])->key());
});

it('supports extension methods registered on the manager', function () {
    CacheletManager::macro('forSynthetic', function (string $value) {
        return $this->for('synthetic')->from(['value' => $value]);
    });

    expect(Cachelet::forSynthetic('ok')->key())
        ->toBe(Cachelet::for('synthetic')->from(['value' => 'ok'])->key());
});
