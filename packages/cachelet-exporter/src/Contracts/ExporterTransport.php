<?php

namespace Oxhq\Cachelet\Exporter\Contracts;

interface ExporterTransport
{
    public function send(array $payload): void;
}
