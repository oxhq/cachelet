# Publishing

`docs/releases.md` is the release process. This file records package topology and Packagist registration details.

## GitHub Repositories

- Root repository: `https://github.com/oxhq/cachelet`
- Split repositories:
  - `https://github.com/oxhq/cachelet-core`
  - `https://github.com/oxhq/cachelet-model`
  - `https://github.com/oxhq/cachelet-query`
  - `https://github.com/oxhq/cachelet-request`
  - `https://github.com/oxhq/cachelet-exporter`

The root repository is the source of truth. Split repositories are release mirrors.

## Public Package Topology

- `oxhq/cachelet`
- `oxhq/cachelet-core`
- `oxhq/cachelet-model`
- `oxhq/cachelet-query`
- `oxhq/cachelet-request`
- `oxhq/cachelet-exporter`

The root repository publishes `oxhq/cachelet`. Focused packages are published from split repositories, not from Packagist indexing monorepo subdirectories.

## Packagist Registration

Packagist requires an authenticated API token to register each repository.

Official API endpoint:

```text
POST https://packagist.org/api/create-package
Authorization: Bearer USERNAME:MAIN_API_TOKEN
Content-Type: application/json

{"repository":"https://github.com/oxhq/cachelet"}
```

After a repository is registered, Packagist can be refreshed with:

```text
POST https://packagist.org/api/update-package
Authorization: Bearer USERNAME:TOKEN
Content-Type: application/json

{"repository":"https://github.com/oxhq/cachelet"}
```

Packagist API docs: `https://packagist.org/apidoc`
