# Benchmarks

Cachelet is meant to add explicit cache coordination without hiding operational cost.

## What To Measure

Measure the paths that matter to your application and compare:

- raw Laravel cache calls
- Cachelet core builders
- Cachelet query builders
- Cachelet request middleware

For each path, capture:

- cache miss latency
- cache hit latency
- stale hit latency
- invalidation latency by exact key and by prefix
- metadata inspection latency

## Recommended Scenarios

### Core

- `remember()` miss vs hit
- `staleWhileRevalidate()` fresh hit vs stale hit
- `invalidate()` vs `invalidatePrefix()`

### Query

- identical SQL/bindings hit path
- different bindings miss path
- table-prefix invalidation

### Request

- same route and vary inputs hit path
- different vary inputs miss path
- namespace invalidation

## What To Record

- driver/store (`array`, `file`, `redis`, database-backed cache)
- payload size
- result count
- whether tags are supported on the store
- whether SWR refresh was synchronous or deferred

## Why The Telemetry Contract Matters

`CacheletTelemetryRecorded` and `cachelet.coordinate.v1` let you attribute benchmark results by:

- module
- store
- prefix
- SWR strategy
- entry state

That means the benchmark story is not just timing numbers. It is a stable projection that external tooling can aggregate without module-specific adapters.
