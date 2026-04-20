<?php

namespace Oxhq\Cachelet\Cloud\Listeners;

use Oxhq\Cachelet\Cloud\Exporting\TelemetryExporter;
use Oxhq\Cachelet\Events\CacheletTelemetryRecorded;

class ExportTelemetryRecord
{
    public function __construct(
        protected TelemetryExporter $exporter,
    ) {}

    public function handle(CacheletTelemetryRecorded $event): void
    {
        $this->exporter->export($event->record);
    }
}
