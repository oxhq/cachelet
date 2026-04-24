<?php

namespace Oxhq\Cachelet\Exporter\Clients;

use Carbon\CarbonImmutable;
use Oxhq\Cachelet\Exporter\Contracts\ExporterClient;
use Oxhq\Cachelet\Exporter\Contracts\ExporterTransport;
use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

class TelemetryExporterClient implements ExporterClient
{
    public function __construct(
        protected ExporterTransport $transport,
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
                'emitter' => 'cachelet-exporter',
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
            'record' => $record->toArray(),
        ]);
    }
}
