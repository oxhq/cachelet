<?php

declare(strict_types=1);

namespace Oxhq\Cachelet\Exporter\Tests\Support;

use Oxhq\Cachelet\Exporter\Contracts\ExporterTransport;

final class InMemoryExporterTransport implements ExporterTransport
{
    public static array $payloads = [];

    public function send(array $payload): void
    {
        self::$payloads[] = $payload;
    }

    public static function reset(): void
    {
        self::$payloads = [];
    }
}
