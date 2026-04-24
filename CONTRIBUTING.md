# Contributing

## Repository Layout

This repository is the public source of truth for the Cachelet package family.

- root package `oxhq/cachelet` - full suite install target and maintainer workspace
- `packages/cachelet-core` - generic runtime
- `packages/cachelet-model` - Eloquent integration
- `packages/cachelet-query` - query integration
- `packages/cachelet-request` - request and response integration
- `packages/cachelet-exporter` - first-party telemetry export integration

Package release mirrors are published from this repository into dedicated public repositories.
Additional maintainer notes are in `docs/monorepo.md`.

## Local Checks

Run these before pushing:

```bash
composer validate --strict
composer validate-packages
composer analyse
composer test
composer format -- --test
```

## Release Expectations

- Keep the root README user-facing.
- Keep package manifests Packagist-ready.
- Keep CI green across the supported Laravel and PHP matrix.
- Keep split repositories and Packagist registrations aligned with the monorepo state.
