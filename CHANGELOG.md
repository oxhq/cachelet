# Changelog

## [0.2.1] - 2026-04-24

Scoped intervention hardening release for the Cachelet family.

### Added

- explicit `scope(...)` support across model, query, and request builders for intervention boundaries
- structured preview, receipt, and verification contracts for scoped interventions

### Fixed

- static analysis regressions in scoped builder integration
- local `composer analyse` execution on Windows by calling the PHPStan binary directly

### Changed

- `main` is reset to a two-commit release history: `v0.1.0` and the current `0.2.x` line

## [0.2.0] - 2026-04-20

Cloud-readiness release for the Cachelet family.

### Added

- canonical coordinate and telemetry contracts across `core`, `model`, `query`, and `request`
- first-party Cloud exporter package with `null`, `log`, `http`, and custom transport support
- operator and benchmark documentation for the family contract

### Renamed

- public package name `oxhq/cachelet-cloud` to `oxhq/cachelet-exporter` to avoid collision with the hosted Cachelet Cloud app naming

### Changed

- coordinate projections now carry module, version, store, and SWR policy data
- store projection resolves the actual repository path instead of assuming the default driver
- SWR telemetry distinguishes policy from actual runtime usage

## [0.1.0] - 2026-04-20

First public release of the Cachelet package family.

### Added

- `oxhq/cachelet` metapackage for installing the full suite.
- `oxhq/cachelet-core` with deterministic cache keys, TTL/SWR, null-safe caching, registry-backed inspection, events, and artisan tooling.
- `oxhq/cachelet-model` with `forModel()`, `$model->cachelet()`, payload shaping, and observer-driven invalidation.
- `oxhq/cachelet-query` with query builder and Eloquent caching macros backed by Cachelet coordinates and invalidation.
- `oxhq/cachelet-request` with request and response cache middleware, route helpers, vary rules, and namespace invalidation.
- CI coverage for Laravel `12/13` and PHP `8.2-8.5`, including Redis and PostgreSQL-backed integration paths.

### Notes

- `0.1.x` is the first public contract for the Cachelet family. It is designed for production use, with room for focused API tightening before `1.0` if needed.

