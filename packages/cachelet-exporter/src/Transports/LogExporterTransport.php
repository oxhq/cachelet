<?php

namespace Oxhq\Cachelet\Exporter\Transports;

use Illuminate\Log\LogManager;
use Oxhq\Cachelet\Exporter\Contracts\ExporterTransport;

class LogExporterTransport implements ExporterTransport
{
    public function __construct(
        protected LogManager $logs,
        protected ?string $channel = null,
    ) {}

    public function send(array $payload): void
    {
        $logger = $this->channel !== null && $this->channel !== ''
            ? $this->logs->channel($this->channel)
            : $this->logs;

        $logger->info('cachelet cloud telemetry exported', [
            'payload' => $payload,
        ]);
    }
}
