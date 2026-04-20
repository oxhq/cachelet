# Monorepo Notes

## Purpose

The repository hosts the full Cachelet package family in one place so shared tests, CI, release tooling, and subtree splits stay aligned.

## Package Boundaries

- root package `oxhq/cachelet` is the full-suite install target.
- `cachelet-core` owns cache coordinates, builders, normalization, TTL/SWR, locking, invalidation, inspection, events, and generic Laravel wiring.
- `cachelet-model` owns model-aware builders, payload shaping, and model lifecycle invalidation.
- `cachelet-query` owns query builder and Eloquent integration.
- `cachelet-request` owns request and response caching integration.

## Public Publishing Model

- `oxhq/cachelet` is published from the root of this repository.
- `oxhq/cachelet-core`, `oxhq/cachelet-model`, `oxhq/cachelet-query`, and `oxhq/cachelet-request` are published from split repositories generated from `packages/*`.
- All packages are versioned together.
