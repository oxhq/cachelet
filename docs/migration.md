# Migration

The safest migration is one painful cache family at a time.

## From Laravel Primitives

Start with an existing `Cache::remember(...)` path that already causes inspection or invalidation pain.

1. Keep the callback unchanged.
2. Move the raw key shape into `Cachelet::for(...)`.
3. Put variable inputs in `from(...)`.
4. Add `ttl(...)`.
5. Add tags, `scope(...)`, or `onStore(...)` only when they solve a real operator problem.

Before:

```php
Cache::remember("users.index.page.$page", 600, fn () => $query->paginate());
```

After:

```php
Cachelet::for('users.index')
    ->from(['page' => $page])
    ->ttl(600)
    ->remember(fn () => $query->paginate());
```

## From A Point Solution

If the app already uses a response-cache, query-cache, or model-cache package:

1. choose one family
2. keep the prefix obvious
3. verify the old invalidation behavior
4. move the family to Cachelet
5. inspect the coordinate output
6. replace the old path only after the new invalidation boundary is clear

Do not migrate the whole cache layer just to standardize syntax. Migrate where the operator contract is useful.

## When To Add The Exporter

Do not add the exporter because the runtime is incomplete.

Add the exporter when the team needs:

- canonical telemetry outside the Laravel process
- shared cache-family evidence in logs or dashboards
- cache behavior data for debugging and audits
- a custom operational surface built from Cachelet records

The runtime contract stays local and useful without the exporter.
