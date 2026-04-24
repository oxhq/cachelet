# Cachelet

Cache orchestration for Laravel.

Cachelet gives Laravel teams one consistent way to define cache keys, apply TTL and stale-while-revalidate behavior, inspect what is stored, and invalidate cached data across generic, model, query, and request-level use cases.

## Packages

| Package | Use it for |
| --- | --- |
| `oxhq/cachelet` | Full suite: core + model + query + request + exporter integrations |
| `oxhq/cachelet-core` | Generic cache builders, TTL/SWR, invalidation, inspection, events, locks |
| `oxhq/cachelet-model` | Eloquent model builders, payload shaping, observer invalidation |
| `oxhq/cachelet-query` | Query builder and Eloquent result caching |
| `oxhq/cachelet-request` | Request and response caching middleware and route integration |
| `oxhq/cachelet-exporter` | First-party exporter for canonical Cachelet telemetry |

## Install

Full suite:

```bash
composer require oxhq/cachelet
```

Focused installs:

```bash
composer require oxhq/cachelet-core
composer require oxhq/cachelet-model
composer require oxhq/cachelet-query
composer require oxhq/cachelet-request
composer require oxhq/cachelet-exporter
```

## Quick Start

Generic cache builder:

```php
use Oxhq\Cachelet\Facades\Cachelet;

$users = Cachelet::for('users.index')
    ->from(['page' => 1, 'role' => 'admin'])
    ->onStore('redis')
    ->ttl('+15 minutes')
    ->remember(fn () => User::query()->where('role', 'admin')->paginate());
```

Model cache builder:

```php
use App\Models\User;
use Oxhq\Cachelet\Traits\UsesCachelet;

class User extends Model
{
    use UsesCachelet;
}

$profile = $user->cachelet()
    ->exclude(['updated_at'])
    ->remember(fn () => $user->fresh());
```

Query cache builder:

```php
$admins = User::query()
    ->where('role', 'admin')
    ->cachelet()
    ->ttl(300)
    ->rememberQuery();
```

Request cache middleware:

```php
Route::get('/users', UserIndexController::class)
    ->name('users.index')
    ->cachelet(600, [
        'vary' => [
            'query' => true,
            'headers' => ['X-Tenant'],
            'auth' => true,
        ],
        'namespace' => 'users',
    ]);
```

## What Cachelet Ships

- Deterministic cache keys built from normalized payloads
- Exact-key invalidation and store-agnostic prefix invalidation
- Stale-while-revalidate with locking and null-safe cache envelopes
- Explicit `onStore(...)` selection for cache values when sidecars or defaults live elsewhere
- Typed cache lifecycle events and coordinate inspection commands
- Sidecar maintenance via `cachelet:prune` for registry and telemetry cleanup
- Focused Laravel integrations for models, queries, and requests
- A first-party Cloud exporter for the canonical telemetry stream

## Operator Contract

Cachelet now exposes one canonical coordinate shape across the family. Every coordinate and telemetry record carries:

- `module`: one of `core`, `model`, `query`, or `request`
- `prefix`, `key`, `ttl`, `version`, `store`, `tags`
- `swr`: refresh mode and lock/grace settings
- `metadata`: module-specific fields such as `model_class`, `table`, `route`, or `method`

When `cachelet.observability.events.enabled` is on, Cachelet emits:

- legacy lifecycle events such as `CacheletHit` and `CacheletInvalidated`
- `CacheletTelemetryRecorded` as the canonical operational event for external consumers

That event wraps a `cachelet.telemetry.v1` projection with the event name, timestamp, coordinate projection, and operation context.

`oxhq/cachelet-exporter` listens to that telemetry stream and exports `cachelet.cloud.export.v1` payloads through `http`, `log`, `null`, or custom transports.

See:

- [`docs/operations.md`](docs/operations.md)
- [`docs/benchmarks.md`](docs/benchmarks.md)

## Support Matrix

- Laravel `12.x` and `13.x`
- PHP `8.2`, `8.3`, `8.4`, and `8.5`
- CI covers Redis plus PostgreSQL-backed cache integration paths

## Stability

`0.2.x` is intended to be production-usable. The package family is still early, so focused API tightening may happen before `1.0` if real-world usage exposes a better contract.

## Development

This repository is both the public source of truth and the install target for `oxhq/cachelet`.
Maintainer and repository workflow documentation lives in `CONTRIBUTING.md` and `docs/monorepo.md`.

