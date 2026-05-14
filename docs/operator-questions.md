# Operator Questions

Cachelet is designed to answer practical cache questions from the CLI and from telemetry.

## What Is Cached Right Now?

List the keys for a family prefix:

```bash
php artisan cachelet:list users.index
```

That tells you which concrete keys Cachelet has recorded for the family.

## What Does This Family Represent?

Inspect one family:

```bash
php artisan cachelet:inspect users.index
```

The coordinate projection answers:

- module
- prefix
- key
- store
- tags
- version
- SWR policy
- scope
- module metadata

## What Is The Smallest Safe Invalidation Boundary?

Prefer the narrowest boundary that matches the problem:

- exact key when one entry is stale
- prefix when one cache family is stale
- tags when the store supports tags and the tag maps cleanly to the domain
- explicit scope when multiple modules share an operational boundary

Avoid whole-store flushes unless the whole store is truly invalid.

## Did Freshness Recover?

Scoped interventions distinguish:

- preview: what would be affected
- receipt: what was executed
- verification: whether fresh evidence returned after execution

That is the difference between "keys were deleted" and "the application recovered useful fresh data."

## How Do I Keep Sidecars Clean?

Run:

```bash
php artisan cachelet:prune
```

This removes orphaned registry entries and expired telemetry sidecars.

## When Should I Export Telemetry?

Export telemetry when these answers need to be shared outside the Laravel process.

The exporter can feed:

- structured logs
- internal dashboards
- audit trails
- operational notebooks
- custom developer tools
