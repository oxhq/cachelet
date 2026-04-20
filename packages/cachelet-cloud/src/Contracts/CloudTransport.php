<?php

namespace Oxhq\Cachelet\Cloud\Contracts;

interface CloudTransport
{
    public function send(array $payload): void;
}
