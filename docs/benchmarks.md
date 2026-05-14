# Benchmarks

Cachelet is meant to add explicit cache coordination without hiding operational cost.

## Reproducible Harness

Run the built-in benchmark harness:

```bash
composer benchmark
```

Optional environment variables:

```bash
CACHELET_BENCH_STORE=array
CACHELET_BENCH_ITERATIONS=25
CACHELET_BENCH_OUTPUT=artifacts/benchmarks/cachelet-benchmark.json
composer benchmark
```

The harness boots a package Testbench app and writes a local JSON report with:

- store
- PHP version
- Laravel version
- iteration count
- core miss/hit timings
- query miss/hit timings
- request miss/hit timings
- prefix invalidation timing

Benchmark reports are local output and are ignored by git. Treat them as reproducibility evidence for the environment where they were generated, not as a universal performance claim.

## What To Measure In Your App

Measure the paths that matter to your application:

- raw Laravel cache calls
- Cachelet core builders
- Cachelet query builders
- Cachelet request middleware
- invalidation by exact key, prefix, tag, and scope
- metadata inspection latency

Record:

- driver/store (`array`, `file`, `redis`, database-backed cache)
- payload size
- result count
- tag support
- whether SWR refresh was synchronous or deferred
- whether a sidecar store differs from the value store

## Recommended Scenarios

### Core

- `remember()` miss vs hit
- `staleWhileRevalidate()` fresh hit vs stale hit
- `invalidate()` vs `invalidatePrefix()`

### Query

- identical SQL and bindings hit path
- different bindings miss path
- table-prefix invalidation

### Request

- same route and vary inputs hit path
- different vary inputs miss path
- namespace invalidation

## Why Telemetry Matters

Timing numbers alone are not enough. `CacheletTelemetryRecorded` and `cachelet.coordinate.v1` let external tooling aggregate benchmark and runtime evidence by:

- module
- store
- prefix
- scope
- SWR strategy
- entry state
