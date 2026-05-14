# Install Matrix

Cachelet ships as a full-suite package plus focused packages for teams that want a smaller dependency surface.

## Default Recommendation

Install `oxhq/cachelet` unless you already know the app only needs one layer.

```bash
composer require oxhq/cachelet
```

That installs:

- `cachelet-core`
- `cachelet-model`
- `cachelet-query`
- `cachelet-request`
- `cachelet-exporter`

## Focused Packages

| Package | Install | Best fit |
| --- | --- | --- |
| `oxhq/cachelet-core` | `composer require oxhq/cachelet-core` | Generic builders, deterministic keys, TTL/SWR, invalidation, inspection, telemetry |
| `oxhq/cachelet-model` | `composer require oxhq/cachelet-model` | Eloquent model variants, payload shaping, observer invalidation |
| `oxhq/cachelet-query` | `composer require oxhq/cachelet-query` | Repeated query results, pagination-aware keys, table-prefix invalidation |
| `oxhq/cachelet-request` | `composer require oxhq/cachelet-request` | Route response caching with explicit vary dimensions |
| `oxhq/cachelet-exporter` | `composer require oxhq/cachelet-exporter` | Exporting canonical telemetry to dashboards, audits, and developer tooling |

## Suggested Combinations

- API backend with explicit service-level caching: `cachelet-core`
- CRUD app with stale model variants: `cachelet-core + cachelet-model`
- reporting/admin UI with expensive repeated queries: `cachelet-core + cachelet-query`
- web app with route response caching: `cachelet-core + cachelet-request`
- team default with one mental model: `cachelet`

## Exporter Boundary

Exporter support is optional. It is not required for Cachelet correctness.

Add `oxhq/cachelet-exporter` when the team wants Cachelet's canonical telemetry outside the Laravel process, such as in logs, internal dashboards, audit trails, or custom operational tooling.
