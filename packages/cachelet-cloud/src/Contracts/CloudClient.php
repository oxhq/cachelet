<?php

namespace Oxhq\Cachelet\Cloud\Contracts;

use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

interface CloudClient
{
    public function export(CacheTelemetryRecord $record): void;
}
