# Publishing

## GitHub

- Root repository: `https://github.com/oxhq/cachelet`
- Split repositories:
  - `https://github.com/oxhq/cachelet-core`
  - `https://github.com/oxhq/cachelet-model`
  - `https://github.com/oxhq/cachelet-query`
  - `https://github.com/oxhq/cachelet-request`
  - `https://github.com/oxhq/cachelet-cloud`
- Current release tag: `v0.2.0`
- Release page: `https://github.com/oxhq/cachelet/releases/tag/v0.2.0`

## Packagist

Packagist requires an authenticated API token to register each repository.

Official API endpoint:

```text
POST https://packagist.org/api/create-package
Authorization: Bearer USERNAME:MAIN_API_TOKEN
Content-Type: application/json

{"repository":"https://github.com/oxhq/cachelet"}
```

Packagist API docs: `https://packagist.org/apidoc`

After the repository is registered, Packagist can be refreshed with:

```text
POST https://packagist.org/api/update-package
Authorization: Bearer USERNAME:TOKEN
Content-Type: application/json

{"repository":"https://github.com/oxhq/cachelet"}
```

Public package topology:

- `oxhq/cachelet`
- `oxhq/cachelet-core`
- `oxhq/cachelet-model`
- `oxhq/cachelet-query`
- `oxhq/cachelet-request`
- `oxhq/cachelet-cloud`

The root repository publishes `oxhq/cachelet`. The focused packages are published from split repositories, not from Packagist indexing subdirectories of the monorepo.
