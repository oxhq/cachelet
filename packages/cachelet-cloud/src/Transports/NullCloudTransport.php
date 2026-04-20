<?php

namespace Oxhq\Cachelet\Cloud\Transports;

use Oxhq\Cachelet\Cloud\Contracts\CloudTransport;

class NullCloudTransport implements CloudTransport
{
    public function send(array $payload): void
    {
        unset($payload);
    }
}
