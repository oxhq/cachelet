# Changelog

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

