# cachelet-exporter

Optional telemetry export for Cachelet records.

`cachelet-exporter` listens for canonical Cachelet telemetry and sends it to a configured transport. Use it to feed logs, dashboards, audit trails, or custom developer tools. It is not required for local cache correctness.

## Install

```bash
composer require oxhq/cachelet-exporter
```

## Best Fit

Add this package when Cachelet is already useful inside the app and the team now wants the same cache evidence outside the Laravel process.

It provides:

- `CacheletTelemetryRecorded` listener wiring
- `cachelet.telemetry.v1` export records
- `cachelet.export.v1` envelopes
- `http`, `log`, and `null` transports
- custom transport classes through the container

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

## Boundary

The exporter is optional. Cachelet's runtime key generation, invalidation, inspection, and telemetry contracts work locally without an external service.

## Docs

- [`../../docs/operations.md`](../../docs/operations.md)
- [`../../docs/install-matrix.md`](../../docs/install-matrix.md)
- [`../../docs/comparison.md`](../../docs/comparison.md)
