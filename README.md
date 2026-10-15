# Compass

A self-hosted status dashboard for GitHub repositories and the Laravel Forge
sites they deploy to. A scheduled collector gathers each repository's latest
commit, `composer.json` and Forge deployment state; members of the configured
GitHub organisations sign in with GitHub to view it.

Compass is a Laravel package: each organisation installs it in its own Laravel
application, configured with its own organisations and repositories.

## Installation

Install the package in a fresh Laravel 13 application:

```
composer require maarheeze/compass
php artisan vendor:publish --tag=compass-config
php artisan migrate
```

The dashboard is served from the root of the application. To serve it under a
prefix instead, set `path` in `config/compass.php`, for example to `/compass`.

The views are styled with Tailwind and use the application's Vite build. Add
the package views as a source in `resources/css/app.css`:

```css
@source '../../vendor/maarheeze/compass/resources/views/**/*.blade.php';
```

## Sign-in

People sign in with their GitHub account. Only active members of at least one
of the configured organisations are let in; membership is checked at sign-in.

```php
// config/compass.php
'organizations' => ['your-org'],
```

Register a GitHub OAuth App at
**Settings → Developer settings → OAuth Apps → New OAuth App**:

- **Homepage URL** — your `APP_URL`
- **Authorization callback URL** — `{APP_URL}/auth/github/callback`, or
  `{APP_URL}/{path}/auth/github/callback` when a `path` is set

Copy the generated client ID and secret into `.env`:

```
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

If an organisation restricts OAuth App access, an owner of that organisation
has to approve the OAuth App before its members can sign in.

## Collector

A scheduled job collects, for every configured repository, the default branch,
the latest commit and `composer.json` from GitHub, and the deployed commit and
latest deployment status from Forge. It authenticates as a dedicated CI user,
never as a signed-in person.

List the repositories, each with the Forge server and site it deploys to:

```php
// config/compass.php
'repositories' => [
    'your-org/website' => [
        'forge' => ['server' => 123456, 'site' => 654321],
    ],
],
```

Add the CI user's credentials to `.env`:

```
COMPASS_GITHUB_TOKEN=
FORGE_API_TOKEN=
```

- **`COMPASS_GITHUB_TOKEN`** — a token of a GitHub account with read access to
  the repositories: a classic token with the `repo` scope, or a fine-grained
  token with read-only *Contents* and *Metadata* permissions.
- **`FORGE_API_TOKEN`** — created under **Forge → Account → API Tokens** by an
  account with access to the configured servers.

The collector runs on the Laravel scheduler and queue, so both need to run:

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
php artisan queue:work
```

Run it by hand with `php artisan compass:collect`.

## Scopes

Sign-in requests two scopes:

| Scope | Why |
| --- | --- |
| `read:org` | Check that you are a member of an allowed organisation, including private memberships |
| `user:email` | Read your account email address |

## Development

Develop against a local Laravel application that requires the package through
a path repository, so changes are picked up without reinstalling:

```json
"repositories": [
    { "type": "path", "url": "../path/to/compass" }
],
"require": {
    "maarheeze/compass": "@dev"
}
```

When the application runs in Sail, also mount the package into the container at
the location the relative symlink in `vendor/maarheeze/compass` points to.

The package's own tests run on Orchestra Testbench after a `composer install`
in this repository.

## License

MIT
