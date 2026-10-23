# Compass

A self-hosted status dashboard for the GitHub repositories of one organization
and the Laravel Forge sites they deploy to. A scheduled collector gathers each
repository's latest commit, `composer.json` and `composer.lock`, the
organization's open issues and pull requests, and each Forge site's latest
deployment; members of the organization sign in with GitHub to view it.

The dashboard shows what needs attention: composer warnings, sites that are
behind on deployment, sites running in debug mode or outside production, sites
and repositories that disappeared, and the open issues and pull requests (all
of them, and the ones assigned to you).

Compass is read-only: it never changes anything on GitHub or Forge.

Compass is a Laravel package: each organization installs it in its own Laravel
application, configured with its own GitHub and Forge organization.

## Installation

Install the package in a fresh Laravel 13 application:

```
composer require maarheeze/compass
php artisan vendor:publish --tag=compass-config
php artisan migrate
```

The dashboard is served from the root of the application. To serve it under a
prefix instead, set `COMPASS_PATH` in `.env`, for example to `/compass`.

The views are styled with Tailwind and use the application's Vite build. Import
the package's colours and add the package views as a source in
`resources/css/app.css`:

```css
@import '../../vendor/maarheeze/compass/resources/css/compass.css';
@source '../../vendor/maarheeze/compass/resources/views/**/*.blade.php';
```

## Sign-in

People sign in with their GitHub account. Only active members of the
configured organization are let in. Membership is checked at sign-in only: a
removed member keeps access until their session ends.

```
COMPASS_GITHUB_ORGANIZATION=your-org
```

Register a GitHub OAuth App (not a GitHub App) under your organization at
**Organization → Settings → Developer settings → OAuth Apps → New OAuth App**:

- **Homepage URL** — any URL, for example your application's repository; it
  is only shown on GitHub's authorization screen
- **Enable Device Flow** — leave off; it is for command-line and headless
  apps, and Compass signs in through the browser
- **Expire user access tokens** — leave off; Compass stores the token for
  later features and does not refresh expired tokens
- **Authorization callback URL** — `{APP_URL}/auth/github/callback`, or
  `{APP_URL}/{COMPASS_PATH}/auth/github/callback` when a path is set

One OAuth App can serve several environments: add a callback URL for each
environment (for example local and production) in the app's settings, and
leave wildcard matching off. Every environment then uses the same client ID
and secret.

Copy the generated client ID and secret into `.env`:

```
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

Sign-in requests two scopes:

| Scope | Why |
| --- | --- |
| `read:org` | Check that you are a member of the organization, including a private membership |
| `user:email` | Read your account email address |

## Collector

Two scheduled commands collect the data, each hourly. They authenticate as a
dedicated CI user, never as a signed-in person.

- **`compass:github-sync`** — every non-archived repository of the
  organization, with its default branch, latest commit, `composer.json` and
  `composer.lock`, and the organization's open issues and pull requests with
  their assignees.
- **`compass:forge-sync`** — the sites of the Forge organization with their
  server and latest deployment, linked to repositories by URL, followed by an
  [inspection of each site](#client).

There is no list of repositories or sites to maintain: everything the tokens
can see is collected. To collect only the sites of some servers, set a regular
expression on the server name:

```
COMPASS_FORGE_ORGANIZATION=your-forge-organization
COMPASS_FORGE_SERVER_PATTERN=/^production-/
```

Add the CI user's credentials to `.env`:

```
COMPASS_GITHUB_TOKEN=
COMPASS_FORGE_TOKEN=
```

- **`COMPASS_GITHUB_TOKEN`** — a token of a GitHub account with read access to
  the organization's repositories: a classic token with the `repo` scope, or a
  fine-grained token owned by the organization with read-only *Contents*,
  *Metadata*, *Issues* and *Pull requests* permissions.
- **`COMPASS_FORGE_TOKEN`** — created under **Forge → Account → API Tokens** by
  an account with access to the Forge organization.

The collector runs on the Laravel scheduler and queue, so both need to run:

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
php artisan queue:work
```

Start a collection by hand with the sync button in the dashboard's header, or
with `php artisan compass:github-sync` and `php artisan compass:forge-sync`.

### Missing sites and repositories

GitHub and Forge stay the source of truth, but Compass never deletes a site or
repository on its own: a sync that returns too little would take its settings
with it. A site or repository that a sync no longer returns is marked
**Missing** and shown on the dashboard. On its page you can **Delete** it, or
**Ignore** the mark when you know why it is gone. When it shows up in a later
sync again, the marks are cleared.

Closed issues and pull requests are simply removed.

## Client

Some things cannot be seen from outside a site: whether debug mode is off,
which environment it runs in, which endpoints should be monitored. The
`maarheeze/compass-client-laravel` package, installed on a site, exposes them
on one endpoint. After collecting the Forge sites, Compass inspects every site
that is not missing through that endpoint and stores the site's report.
Saving a site's client settings inspects that site straight away. The site
configures itself: what Compass checks is set in the site's own config, not in
Compass.

Generate the key Compass sends to every client:

```
php artisan compass:key-generate
```

This writes the default key, `COMPASS_CLIENT_KEY`, to Compass's `.env` and
prints it. Use the same key as `COMPASS_CLIENT_KEY` on every site. The command
refuses to replace a key that is already set, since every site using it would
have to be updated too; pass `--force` to replace it anyway, or `--show` to only
print a new key.

The client answers on `/_compass`. Change the default path for all sites with
`COMPASS_CLIENT_PATH`.

A site can have its own path and key: on the site's page in Compass, **Edit**
the client settings. A field left empty uses the default. The site's page shows
the path and key in use, so a site can be given a key of its own and set up
from there. A site without its own key and without a default key is not read.

On each site's page, Compass shows what the client reported and when:

| Status | Meaning |
| --- | --- |
| Installed | The client answered; its report is stored |
| Not installed | The site has no client; any earlier report is cleared |
| Key refused | The client refused the key; shown on the dashboard |
| Unknown | The site gave no usable answer; the last report is kept |

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
