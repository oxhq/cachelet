<?php

namespace Oxhq\Cachelet\Exporter\Contracts;

use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

interface ExporterClient
{
    public function export(CacheTelemetryRecord $record): void;
}
