<?php

namespace Oxhq\Cachelet\Cloud\Clients;

use Carbon\CarbonImmutable;
use Oxhq\Cachelet\Cloud\Contracts\CloudClient;
use Oxhq\Cachelet\Cloud\Contracts\CloudTransport;
use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

class TelemetryCloudClient implements CloudClient
{
    public function __construct(
        protected CloudTransport $transport,
        protected array $source = [],
    ) {}

    public function export(CacheTelemetryRecord $record): void
    {
        $this->transport->send([
            'contract' => 'cachelet.cloud.export.v1',
            'sent_at' => CarbonImmutable::now()->toIso8601String(),
            'source' => array_filter([
                'app' => $this->source['app'] ?? null,
                'environment' => $this->source['environment'] ?? null,
                'instance' => $this->source['instance'] ?? null,
                'emitter' => 'cachelet-cloud',
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
            'record' => $record->toArray(),
        ]);
    }
}
