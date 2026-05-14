# Comparison

Cachelet is not a replacement for Laravel's cache API. It is an operating layer on top of it.

## Laravel Primitives

Laravel already gives you strong primitives:

- `Cache::remember`
- `Cache::rememberForever`
- locks
- tags on supported stores
- `Cache::flexible` for stale-while-revalidate

Use raw Laravel primitives when:

- the cache key is local and obvious
- invalidation is close to the write path
- the app does not need shared cache visibility
- the team is comfortable with ad hoc keys

Use Cachelet when:

- multiple cache surfaces should share one contract
- key generation needs to be deterministic and inspectable
- invalidation needs a smaller boundary than `flush`
- teams need one telemetry shape across core, model, query, and request caches
- operators need preview, receipt, and verification contracts for scoped interventions

## Point Solutions

Response-cache, query-cache, and model-cache packages are valid when the app has one narrow caching problem.

Cachelet is a better fit when the team wants:

- one cache family vocabulary
- one coordinate contract
- one inspection story
- one invalidation and telemetry model
- one optional path into external telemetry tooling

## Exporter

The exporter is optional. It does not make the OSS runtime complete; the runtime is already useful locally.

Add the exporter when cache evidence needs to feed logs, dashboards, audits, or custom developer tools.

## Non-Goals

Cachelet `0.2.x` does not claim:

- automatic relational invalidation for arbitrary query graphs
- proxy or CDN orchestration
- Blade fragment caching
- perfect zero-config inference for every cache use case

The product thesis is explicit cache orchestration for Laravel, with a clean attach point into an optional control plane.
