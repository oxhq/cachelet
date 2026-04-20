# Cachelet

Cache orchestration for Laravel.

Cachelet gives Laravel teams one consistent way to define cache keys, apply TTL and stale-while-revalidate behavior, inspect what is stored, and invalidate cached data across generic, model, query, and request-level use cases.

## Packages

| Package | Use it for |
| --- | --- |
| `oxhq/cachelet` | Full suite: core + model + query + request integrations |
| `oxhq/cachelet-core` | Generic cache builders, TTL/SWR, invalidation, inspection, events, locks |
| `oxhq/cachelet-model` | Eloquent model builders, payload shaping, observer invalidation |
| `oxhq/cachelet-query` | Query builder and Eloquent result caching |
| `oxhq/cachelet-request` | Request and response caching middleware and route integration |

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
```

## Quick Start

Generic cache builder:

```php
use Oxhq\Cachelet\Facades\Cachelet;

$users = Cachelet::for('users.index')
    ->from(['page' => 1, 'role' => 'admin'])
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
- Typed cache lifecycle events and coordinate inspection commands
- Focused Laravel integrations for models, queries, and requests

## Support Matrix

- Laravel `12.x` and `13.x`
- PHP `8.2`, `8.3`, `8.4`, and `8.5`
- CI covers Redis plus PostgreSQL-backed cache integration paths

## Stability

`0.1.x` is intended to be production-usable. The package family is still early, so focused API tightening may happen before `1.0` if real-world usage exposes a better contract.

## Development

This repository is both the public source of truth and the install target for `oxhq/cachelet`.
Maintainer and repository workflow documentation lives in `CONTRIBUTING.md` and `docs/monorepo.md`.

