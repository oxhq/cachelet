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
composer benchmark
```

Use the smallest relevant subset while iterating, then run the full set before a release PR.

## Pull Requests

- Open focused pull requests with one behavioral change or documentation improvement.
- Include tests for runtime changes.
- Update public documentation when behavior, install instructions, commands, or package boundaries change.
- Keep generated benchmark reports, local scratch files, credentials, and private planning notes out of commits.
- Use the pull request template and paste the exact verification commands you ran.

## Issues

Use the bug report template for reproducible failures and the feature request template for proposed API, command, or package additions.

For bugs, include:

- affected Cachelet package and version
- Laravel, PHP, and cache store versions
- the smallest reproduction or failing test
- expected and actual behavior

Security reports belong in the private process described in `SECURITY.md`, not in public issues.

## Public Surface

This is a public package repository. Do not commit:

- local credentials or environment files
- generated scratch output
- private planning notes
- maintainer-only release drafts
- tool-specific local workflow files

Public docs should describe shipped behavior, supported workflows, and known limits.

## Release Expectations

- Keep the root README user-facing.
- Keep package manifests Packagist-ready.
- Keep CI green across the supported Laravel and PHP matrix.
- Keep split repositories and Packagist registrations aligned with the monorepo state.
