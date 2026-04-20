<?php

declare(strict_types=1);

namespace Oxhq\Cachelet\Cloud\Tests\Support;

use Oxhq\Cachelet\Cloud\Contracts\CloudTransport;

final class InMemoryCloudTransport implements CloudTransport
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
