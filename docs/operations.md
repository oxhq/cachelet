# Operations

Cachelet answers the same operator questions across `core`, `model`, `query`, and `request`.

## Canonical Coordinate

Every builder resolves to `cachelet.coordinate.v1` with:

- `module`: `core`, `model`, `query`, or `request`
- `prefix`
- `key`
- `ttl`
- `version`
- `store`
- `tags`
- `swr`
- `metadata`

`swr` is the policy projection for that coordinate:

- `capable`: whether the coordinate can participate in SWR
- `configured`: whether SWR is configured for that coordinate
- refresh and lock/grace settings

Module-specific metadata is normalized rather than inferred:

- `model`: `model_class`, `model_key`
- `query`: `table`, `connection`
- `request`: `route`, `method`, `path`

## Canonical Telemetry

When `cachelet.observability.events.enabled` is enabled, Cachelet emits:

- convenience lifecycle events such as `CacheletHit`, `CacheletMiss`, `CacheletStored`, `CacheletInvalidated`
- `CacheletTelemetryRecorded` as the operational contract

`CacheletTelemetryRecorded` wraps `cachelet.telemetry.v1`:

- `event`
- `occurred_at`
- `coordinate`
- `context`

Context answers the runtime questions Cloud and operators usually need:

- `access_strategy`
- `entry_state`
- `background`
- `swr_runtime`
- `reason`
- `keys`
- `value_type`

`swr_runtime` is the access-path projection:

- `requested`
- `background_refresh`
- `served_stale`
- `entry_state`

## Query Guarantees

`cachelet-query` gives deterministic keys from:

- SQL
- bindings
- connection
- pagination inputs

It guarantees explicit invalidation by prefix/tag. It does not guarantee automatic relational invalidation in `0.2.x`.

## Request Guarantees

`cachelet-request` guarantees explicit vary dimensions and explicit bypass rules.

By default:

- cacheable methods: `GET`, `HEAD`
- cacheable statuses: `200`
- bypassed: streamed responses, binary file responses

Vary dimensions are opt-in and inspectable through the request coordinate metadata.
