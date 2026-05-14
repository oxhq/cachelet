# Releases

This repository is the release source for the Cachelet package family.

## Release Shape

Each public release should produce:

- a root tag such as `v0.2.3`
- a GitHub release on `oxhq/cachelet`
- split repository updates for the focused packages
- Packagist visibility for the root package and split packages
- an updated changelog entry

## Local Gate

Run the full local gate before tagging:

```bash
composer validate --strict
composer validate-packages
composer format -- --test
composer analyse
composer test
composer test:exporter
composer benchmark
```

`composer benchmark` writes ignored local JSON under `artifacts/benchmarks/`. Use it as release evidence, not as a committed artifact.

## CI Gate

The hosted release gate is stronger than a local pass. A release is not complete until GitHub Actions is green for:

- tests workflow
- release workflow
- split workflow

The matrix covers supported Laravel/PHP combinations and integration paths for Redis and PostgreSQL-backed cache stores.

## Versioning

Before `1.0`, Cachelet can still tighten APIs when real usage proves a better contract. Keep those changes explicit in `CHANGELOG.md`.

Use patch releases for:

- bug fixes
- docs and examples
- package metadata fixes
- benchmark harness fixes

Use minor releases for:

- new public APIs
- new commands
- expanded package contracts
- compatibility changes that users should notice

## Split Repositories

The root repository owns source changes. Focused package repositories are published from subtree splits:

- `oxhq/cachelet-core`
- `oxhq/cachelet-model`
- `oxhq/cachelet-query`
- `oxhq/cachelet-request`
- `oxhq/cachelet-exporter`

Do not edit split repositories as the source of truth unless recovering from a publishing incident.

## Release Checklist

1. Confirm the working tree contains only intended public changes.
2. Confirm public docs do not include local credentials, generated benchmark JSON, private planning notes, or maintainer-only drafts.
3. Run the local gate.
4. Update `CHANGELOG.md`.
5. Tag the release.
6. Watch GitHub Actions for the tag and default branch.
7. Confirm split repositories received the tag.
8. Confirm Packagist shows the new versions.
9. Review the GitHub release text for accurate claims.

## Rollback

If a package release is bad:

- publish a fixed patch release instead of rewriting published history
- update the GitHub release notes with the known issue
- keep Packagist and split repositories aligned with the root repository

Only force-push split repositories as part of the documented split workflow or an explicit recovery procedure.
