# Cachelet

Declarative caching for Laravel with a single fluent builder API.

## What it does

- Builds deterministic cache keys from a prefix plus normalized payload.
- Supports generic and model-driven cache builders through `Cachelet::for()` and `Cachelet::forModel()`.
- Tracks stored keys in a store-agnostic registry so listing, inspection, and prefix flushing work on standard Laravel cache drivers.
- Supports stale-while-revalidate, typed cache events, and model observer invalidation.

## Install

```bash
composer require oxhq/cachelet
```

Publish the config if you want to override defaults:

```bash
php artisan vendor:publish --tag=cachelet-config
```

## Usage

```php
use Oxhq\Cachelet\Facades\Cachelet;

$value = Cachelet::for('users.index')
    ->from(['page' => 1, 'filters' => ['active' => true]])
    ->ttl('+15 minutes')
    ->remember(fn () => User::query()->where('active', true)->paginate());
```

```php
$value = Cachelet::for('users.show')
    ->from(['id' => $user->id])
    ->staleWhileRevalidate(
        fn () => $user->fresh(),
        fn () => $user
    );
```

### Model integration

```php
use Oxhq\Cachelet\Traits\UsesCachelet;

class User extends Model
{
    use UsesCachelet;
}

$builder = $user->cachelet()
    ->exclude(['updated_at']);

$value = $builder->remember(fn () => $user->fresh());
$key = $builder->key();
```

Model builders also support `only()`, `exclude()`, `withDates()`, and `withTimestamps()` for payload shaping.

## Commands

- `php artisan cachelet:list {prefix}`
- `php artisan cachelet:inspect {prefix}`
- `php artisan cachelet:flush {prefix}`

## Config

The published config covers:

- default TTL and cache key prefix
- typed event toggles
- observer auto-registration
- stale-while-revalidate lock settings
- model serialization defaults
