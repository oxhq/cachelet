# cachelet-exporter

Read-only split of the Cachelet monorepo package at `packages/cachelet-exporter`.

First-party Cloud exporter for Cachelet's canonical telemetry stream.

## Install

```bash
composer require oxhq/cachelet-exporter
```

## Features

- listens for `CacheletTelemetryRecorded`
- exports canonical `cachelet.telemetry.v1` records
- ships configurable `http`, `log`, and `null` transports
- allows custom transport classes through the container
- publishes `cachelet-exporter.php` for endpoint and source metadata

## Example

```php
return [
    'enabled' => true,
    'transport' => 'http',
    'client' => [
        'endpoint' => env('CACHELET_EXPORTER_ENDPOINT'),
        'token' => env('CACHELET_EXPORTER_TOKEN'),
    ],
];
```

When Cachelet emits `CacheletTelemetryRecorded`, `cachelet-exporter` wraps the
canonical record in `cachelet.cloud.export.v1` and forwards it through the
configured transport.

## Custom transports

Set `cachelet-exporter.transport` to a class name that implements
`Oxhq\\Cachelet\\Exporter\\Contracts\\ExporterTransport`. The service provider
resolves that class through Laravel's container, so test doubles and custom
adapters can be injected without patching core Cachelet code.
