<?php

namespace Oxhq\Cachelet\Exporter\Listeners;

use Oxhq\Cachelet\Events\CacheletTelemetryRecorded;
use Oxhq\Cachelet\Exporter\Exporting\TelemetryExporter;

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
