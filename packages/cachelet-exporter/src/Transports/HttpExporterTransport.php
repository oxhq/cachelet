<?php

namespace Oxhq\Cachelet\Exporter\Transports;

use Illuminate\Http\Client\Factory;
use Oxhq\Cachelet\Exporter\Contracts\ExporterTransport;

class HttpExporterTransport implements ExporterTransport
{
    public function __construct(
        protected Factory $http,
        protected string $endpoint,
        protected ?string $token = null,
        protected int $timeout = 3,
        protected array $headers = [],
    ) {}

    public function send(array $payload): void
    {
        $request = $this->http
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout);

        if ($this->token !== null && $this->token !== '') {
            $request = $request->withToken($this->token);
        }

        if ($this->headers !== []) {
            $request = $request->withHeaders($this->headers);
        }

        $request->post($this->endpoint, $payload)->throw();
    }
}
