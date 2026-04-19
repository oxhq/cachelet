# Changelog

## [Unreleased]

- Collapse the package onto a single fluent builder architecture rooted in `Cachelet::for()` and `Cachelet::forModel()`.
- Remove the incomplete duplicate `Core`/`Support` refactor branch and the standalone `ModelCachelet` API.
- Add a store-agnostic registry/metadata layer that powers list, inspect, and prefix flush commands on standard Laravel cache drivers.
- Standardize invalidation around registry-backed prefix flushing, with cache tags treated as an optional optimization when the store supports them.
- Keep stale-while-revalidate, typed cache events, model observer invalidation, and model payload shaping on the canonical builders.
- Rewrite the test suite around the shipped public API and add usage documentation.
