<?php

namespace Oxhq\Cachelet\Cloud\Exporting;

use Illuminate\Log\LogManager;
use Oxhq\Cachelet\Cloud\Contracts\CloudClient;
use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;
use Throwable;

class TelemetryExporter
{
    public function __construct(
        protected CloudClient $client,
        protected LogManager $logs,
        protected bool $enabled = false,
        protected bool $throwFailures = false,
    ) {}

    public function export(CacheTelemetryRecord $record): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $this->client->export($record);
        } catch (Throwable $exception) {
            if ($this->throwFailures) {
                throw $exception;
            }

            $this->logs->warning('cachelet cloud export failed', [
                'event' => $record->event,
                'module' => $record->coordinate->module,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
