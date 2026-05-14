# Cachelet Documentation

Cachelet is organized around one idea: Laravel cache state should be inspectable, attributable, and recoverable.

Use these docs based on what you are trying to do.

## Adopt Cachelet

- [`install-matrix.md`](install-matrix.md): choose the full suite or focused packages.
- [`migration.md`](migration.md): move from raw Laravel cache calls or narrow cache packages.
- [`comparison.md`](comparison.md): decide when Cachelet is the right tool.

## Operate Cachelet

- [`operator-questions.md`](operator-questions.md): list, inspect, invalidate, and verify cache families.
- [`operations.md`](operations.md): understand coordinates, telemetry, SWR, query guarantees, request guarantees, and sidecars.
- [`benchmarks.md`](benchmarks.md): run the benchmark harness and capture app-specific evidence.

## Maintain The Project

- [`monorepo.md`](monorepo.md): package layout and ownership.
- [`releases.md`](releases.md): release gates, split repositories, GitHub releases, and Packagist.
- [`publishing.md`](publishing.md): Packagist registration and package topology details.

## Package READMEs

- [`../packages/cachelet-core/README.md`](../packages/cachelet-core/README.md)
- [`../packages/cachelet-model/README.md`](../packages/cachelet-model/README.md)
- [`../packages/cachelet-query/README.md`](../packages/cachelet-query/README.md)
- [`../packages/cachelet-request/README.md`](../packages/cachelet-request/README.md)
- [`../packages/cachelet-exporter/README.md`](../packages/cachelet-exporter/README.md)
