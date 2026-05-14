# cachelet-model

Eloquent model caching with Cachelet coordinates.

`cachelet-model` gives model-derived cache entries a stable module identity, inspectable key payloads, and observer-driven invalidation for model cache families.

## Install

```bash
composer require oxhq/cachelet-model
```

## Best Fit

Use this package when stale model variants and invalidation blast radius are the main pain.

It provides:

- `Cachelet::forModel(...)`
- `$model->cachelet()`
- `only(...)`, `exclude(...)`, `withDates()`, and `withTimestamps()`
- observer-driven invalidation for model prefixes
- canonical `module = model` coordinates and telemetry

## Example

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

## Docs

- [`../../docs/operations.md`](../../docs/operations.md)
- [`../../docs/migration.md`](../../docs/migration.md)
- [`../../docs/install-matrix.md`](../../docs/install-matrix.md)
