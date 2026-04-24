<?php

namespace Oxhq\Cachelet\Exporter\Transports;

use Oxhq\Cachelet\Exporter\Contracts\ExporterTransport;

class NullExporterTransport implements ExporterTransport
{
    public function send(array $payload): void
    {
        unset($payload);
    }
}
