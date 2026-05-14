# Cachelet

[![Tests](https://github.com/oxhq/cachelet/actions/workflows/tests.yml/badge.svg)](https://github.com/oxhq/cachelet/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/oxhq/cachelet.svg)](https://packagist.org/packages/oxhq/cachelet)
[![License](https://img.shields.io/packagist/l/oxhq/cachelet.svg)](LICENSE)

**The cache operations layer for Laravel.**

Stop flushing blind.

Cachelet gives every important cache entry a stable coordinate: what it belongs to, where it lives, how it was keyed, how long it should live, and how it can be invalidated without reaching for a whole-store flush.

Laravel already gives you excellent cache primitives. Cachelet turns those primitives into an inspectable operating model across app-level values, Eloquent models, query results, route responses, and optional telemetry exports.

```php
use Oxhq\Cachelet\Facades\Cachelet;

$users = Cachelet::for('users.index')
    ->from(['page' => 1, 'role' => 'admin'])
    ->onStore('redis')
    ->ttl('+15 minutes')
    ->remember(fn () => User::query()->where('role', 'admin')->paginate());
```

## Why Cachelet

Production cache bugs are rarely about calling `remember()` wrong. They are about invisible state:

- Which cache family owns this key?
- Which store was used?
- Which request, model, query, or service produced it?
- What is safe to invalidate?
- Did the fresh path recover after the intervention?

Cachelet makes those answers first-class.

## What You Get

- **Deterministic coordinates** for core, model, query, and request caches.
- **Stable key generation** from normalized payloads.
- **TTL and stale-while-revalidate** with lock-aware refresh behavior.
- **Scoped invalidation** by key, prefix, tag, or explicit scope.
- **Local inspection commands** for listing, inspecting, flushing, and pruning cache families.
- **Canonical telemetry** through `cachelet.telemetry.v1`.
- **Optional exporter support** when cache visibility needs to feed dashboards, audit trails, or developer tooling.

Cachelet is useful without an external service. The exporter is a tooling bridge for teams that want canonical cache evidence outside the Laravel process.

## Install

Most teams should start with the full suite:

```bash
composer require oxhq/cachelet
```

Use focused packages when a project only needs one layer:

| Package | Use it for |
| --- | --- |
| `oxhq/cachelet` | Full suite: core + model + query + request + exporter |
| `oxhq/cachelet-core` | Generic builders, keys, TTL/SWR, invalidation, inspection, telemetry |
| `oxhq/cachelet-model` | Eloquent model caching, payload shaping, observer invalidation |
| `oxhq/cachelet-query` | Query builder and Eloquent result caching |
| `oxhq/cachelet-request` | Request/response caching middleware and route integration |
| `oxhq/cachelet-exporter` | Optional telemetry export for external tooling |

See the full install guide: [`docs/install-matrix.md`](docs/install-matrix.md).

## Quick Tour

### Core Values

```php
$report = Cachelet::for('reports.sales')
    ->from(['from' => '2026-01-01', 'to' => '2026-01-31'])
    ->ttl(1800)
    ->remember(fn () => $service->salesReport());
```

### Eloquent Models

```php
use Oxhq\Cachelet\Traits\UsesCachelet;

class User extends Model
{
    use UsesCachelet;
}

$profile = $user->cachelet()
    ->exclude(['updated_at'])
    ->ttl(300)
    ->remember(fn () => $user->fresh());
```

### Queries

```php
$admins = User::query()
    ->where('role', 'admin')
    ->cachelet()
    ->ttl(300)
    ->rememberQuery();
```

### Route Responses

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

More examples live in [`examples/`](examples).

## Operator Commands

Cachelet keeps enough sidecar state to make cache families visible from the CLI:

```bash
php artisan cachelet:list users.index
php artisan cachelet:inspect users.index
php artisan cachelet:flush users.index
php artisan cachelet:prune
```

The operator guide explains what each answer means: [`docs/operator-questions.md`](docs/operator-questions.md).

## The Contract

Every coordinate resolves to `cachelet.coordinate.v1` with:

- `module`: `core`, `model`, `query`, or `request`
- `prefix`
- `key`
- `ttl`
- `version`
- `store`
- `tags`
- `swr`
- `metadata`

When observability events are enabled, Cachelet emits `CacheletTelemetryRecorded` records using `cachelet.telemetry.v1`.

See [`docs/operations.md`](docs/operations.md) for the full runtime contract.

## When To Use Cachelet

Use raw Laravel cache calls when the cache is simple, local, and obvious.

Use a narrow point solution when the app only needs one specialized job, such as response caching.

Use Cachelet when a Laravel app has more than one cache surface and the team needs one vocabulary for keys, scopes, stores, invalidation, inspection, and telemetry.

Comparison guide: [`docs/comparison.md`](docs/comparison.md).

## Docs

- Start here: [`docs/README.md`](docs/README.md)
- Install matrix: [`docs/install-matrix.md`](docs/install-matrix.md)
- Migration guide: [`docs/migration.md`](docs/migration.md)
- Operations contract: [`docs/operations.md`](docs/operations.md)
- Operator questions: [`docs/operator-questions.md`](docs/operator-questions.md)
- Benchmarks: [`docs/benchmarks.md`](docs/benchmarks.md)
- Releases and publishing: [`docs/releases.md`](docs/releases.md)

## Support Matrix

- Laravel `12.x` and `13.x`
- PHP `8.2`, `8.3`, `8.4`, and `8.5`
- CI covers Redis plus PostgreSQL-backed cache integration paths

## Stability

`0.2.x` is intended to be production-usable. The package family is still early, so focused API tightening may happen before `1.0` if real usage proves a better contract.

Cachelet does not claim automatic relational invalidation for arbitrary query graphs, CDN orchestration, Blade fragment caching, or perfect zero-config inference for every cache use case.

## Community

- Contributing: [`CONTRIBUTING.md`](CONTRIBUTING.md)
- Security reports: [`SECURITY.md`](SECURITY.md)
- Support policy: [`SUPPORT.md`](SUPPORT.md)
- Code of conduct: [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md)

## Repository

This monorepo is the public source of truth for `oxhq/cachelet` and the focused split packages. Maintainer workflow details live in [`CONTRIBUTING.md`](CONTRIBUTING.md), [`docs/monorepo.md`](docs/monorepo.md), and [`docs/releases.md`](docs/releases.md).
