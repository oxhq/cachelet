# Operations Contract

Cachelet is built around cache coordinates. A coordinate is the stable description of a cache entry: module, prefix, key, store, TTL, tags, SWR policy, scope, and metadata.

That contract lets a Laravel team inspect and recover cache families without treating the cache store as a black box.

## Coordinates

Every builder resolves to `cachelet.coordinate.v1` with:

- `module`: `core`, `model`, `query`, or `request`
- `prefix`
- `key`
- `ttl`
- `version`
- `store`
- `tags`
- `swr`
- `metadata`
- `scope`

Module-specific metadata is normalized:

- `model`: model class and model key
- `query`: table, connection, SQL, bindings, and pagination inputs
- `request`: route, method, path, and vary dimensions

## Stale-While-Revalidate

The `swr` projection describes both policy and runtime behavior.

Policy answers:

- whether the coordinate can participate in SWR
- whether SWR is configured
- refresh mode
- lock TTL
- grace TTL

Runtime telemetry can then report:

- whether SWR was requested
- whether stale data was served
- whether refresh ran in the background
- whether the entry was fresh, stale, missing, or refreshed

## Telemetry

When `cachelet.observability.events.enabled` is enabled, Cachelet emits:

- convenience lifecycle events such as `CacheletHit`, `CacheletMiss`, `CacheletStored`, and `CacheletInvalidated`
- `CacheletTelemetryRecorded` as the canonical operational event

`CacheletTelemetryRecorded` wraps `cachelet.telemetry.v1`:

- `event`
- `occurred_at`
- `coordinate`
- `context`

Context carries runtime details such as access strategy, entry state, SWR runtime, invalidation reason, affected keys, and value type.

## Invalidation

Cachelet supports exact-key and family invalidation through the same coordinate model.

Common boundaries:

- exact key
- prefix
- supported store tags
- explicit scope
- inferred module scope

Scoped interventions return preview, receipt, and verification projections. That distinction matters: deleting keys is not the same as proving fresh data recovered.

## Query Guarantees

`cachelet-query` creates deterministic keys from:

- SQL
- bindings
- connection
- table grouping
- pagination inputs

It guarantees explicit invalidation by query-table prefix and tags. It does not guarantee automatic relational invalidation for arbitrary query graphs in `0.2.x`.

## Request Guarantees

`cachelet-request` gives route response caching with explicit vary dimensions and bypass behavior.

Defaults:

- cacheable methods: `GET`, `HEAD`
- cacheable statuses: `200`
- bypassed responses: streamed responses and binary file responses

Vary dimensions are opt-in and inspectable through request coordinate metadata.

## Sidecars

Cachelet keeps registry and telemetry sidecars so inspection, scoped interventions, and verification can work across modules.

If cache values are cleared outside Cachelet or sidecars outlive cached entries, run:

```bash
php artisan cachelet:prune
```

When values should live on a specific store, use `onStore(...)` so the coordinate projection, invalidation path, and sidecar verification point at the real backing store.
