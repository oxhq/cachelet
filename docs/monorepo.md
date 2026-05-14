# Monorepo

This repository is the public source of truth for the Cachelet package family.

## Packages

- `oxhq/cachelet`: full-suite install target
- `packages/cachelet-core`: coordinates, builders, normalization, TTL/SWR, locking, invalidation, inspection, events, and generic Laravel wiring
- `packages/cachelet-model`: Eloquent model integration and observer invalidation
- `packages/cachelet-query`: query builder and Eloquent result caching
- `packages/cachelet-request`: request/response route caching
- `packages/cachelet-exporter`: optional telemetry export for external tooling

## Source Of Truth

Make source changes in this repository. Split repositories are release mirrors and should not be edited directly during normal development.

## Local Workflow

Useful commands:

```bash
composer validate --strict
composer validate-packages
composer format -- --test
composer analyse
composer test
composer test:exporter
composer benchmark
```

`composer benchmark` writes ignored local output under `artifacts/benchmarks/`.

## Release Workflow

Release gates and publishing steps are documented in [`releases.md`](releases.md) and [`publishing.md`](publishing.md).
