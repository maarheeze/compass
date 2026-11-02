# maarheeze/compass

**A self-hosted status dashboard for the GitHub repositories of one organization and the Laravel Forge sites they deploy to.**

A scheduled collector gathers each repository's latest commit, composer files, open issues and pull requests, and each Forge site's latest deployment. The dashboard shows what needs attention; members of the organization sign in with GitHub to view it. Compass is read-only: it never changes anything on GitHub or Forge.

## Installation

```bash
composer require maarheeze/compass
php artisan vendor:publish --tag=compass-config
php artisan migrate
```

The views use the application's Vite build. Add the package to `resources/css/app.css`:

```css
@import '../../vendor/maarheeze/compass/resources/css/compass.css';
@source '../../vendor/maarheeze/compass/resources/views/**/*.blade.php';
```

The dashboard is served from the root of the application. If you want to serve it under a prefix:

```dotenv
COMPASS_PATH=/compass
```

## Configuration

```dotenv
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=

COMPASS_GITHUB_ORGANIZATION=your-org
COMPASS_GITHUB_TOKEN=

COMPASS_FORGE_AUTO_DEPLOY_GRACE_MINUTES=5
COMPASS_FORGE_ORGANIZATION=your-forge-organization
COMPASS_FORGE_SERVER_NAME_PREFIX=production-
COMPASS_FORGE_TOKEN=

COMPASS_CLIENT_KEY=
COMPASS_CLIENT_PATH=/_compass

COMPASS_LOG_CHANNEL=

COMPASS_DISPLAY_TIMEZONE=Europe/Amsterdam
COMPASS_DISPLAY_DATE_TIME_FORMAT="Y-m-d H:i:s"
COMPASS_ITEMS_PER_PAGE=25
```

- `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` — a GitHub OAuth App of the organization, with `{APP_URL}/auth/github/callback` as callback URL (prefixed with `COMPASS_PATH` when set). Only active members of the organization are let in.
- `COMPASS_GITHUB_TOKEN` — a token with read access to the organization's repositories: a classic token with the `repo` scope, or a fine-grained token with read-only *Contents*, *Metadata*, *Issues* and *Pull requests* permissions.
- `COMPASS_FORGE_TOKEN` — created under **Forge → Account → API Tokens**. The optional server name prefix limits the sites to those of servers whose name starts with it. A site with auto-deploy enabled gets the grace minutes to deploy a new commit before its deployment counts as overdue.
- `COMPASS_CLIENT_KEY` and `COMPASS_CLIENT_PATH` — the default key and path for reading [the client](#client); a site can override both on its page.
- `COMPASS_LOG_CHANNEL` — the log channel the collector writes to: an info line when a collection starts and ends, a debug line per page, composer file, site and endpoint, each with the repository, site or endpoint it is about. Empty writes to the application's default channel.
- `COMPASS_DISPLAY_TIMEZONE` — the timezone dates and times are shown in on the dashboard. Stored times are not affected.
- `COMPASS_DISPLAY_DATE_TIME_FORMAT` — the [PHP date format](https://www.php.net/manual/en/datetime.format.php) dates and times are shown in where an exact moment matters, such as the runs and the endpoint history.
- `COMPASS_ITEMS_PER_PAGE` — the number of items a paginated list shows per page, such as the runs and the endpoint history.

### Sources

Compass collects from the sources listed under `sources` in `config/compass.php`, the way Laravel lists its filesystem disks: each source has a name of your choosing, the driver it uses and the settings that driver needs. The config ships a `github` and a `forge` source on the env names above, so one GitHub organization and one Forge organization work from `.env` alone.

- A `github` source collects every non-archived repository of its `organization`, read with its `token`: the token of a CI user, never a signed-in person's token.
- A `forge` source collects the sites of its `organization`, read with its `token`, limited to the servers whose name starts with its `server_name_prefix` (empty for every server). A site with auto-deploy enabled gets its `auto_deploy_grace_minutes` to deploy a new commit before its deployment counts as overdue.

For anything else, such as a second organization or no Forge at all, publish the config and edit `sources`:

```php
'sources' => [
    'acme' => [
        'driver' => 'github',
        'organization' => env('COMPASS_ACME_ORGANIZATION'),
        'token' => env('COMPASS_ACME_TOKEN'),
    ],
    'acme-labs' => [
        'driver' => 'github',
        'organization' => env('COMPASS_ACME_LABS_ORGANIZATION'),
        'token' => env('COMPASS_ACME_LABS_TOKEN'),
    ],
],
```

Every source is checked the first time Compass needs one: a source naming a driver Compass does not have, or missing a setting its driver needs, fails before anything is collected.

## Usage

The collector runs on the Laravel scheduler and queue, so both need to run. Each sync and endpoint check is a job batch, so the host needs the `job_batches` table (part of the default Laravel skeleton, or created with `php artisan make:queue-batches-table`):

```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
php artisan queue:work
```

Both syncs run hourly, and can be started from the dashboard's header or by hand. How often each task runs is set per task under `schedule` in `config/compass.php`, from every five minutes to weekly; a task left out is not scheduled:

```bash
php artisan compass:github-sync
php artisan compass:forge-sync
```

The endpoints the sites report are checked every 15 minutes, or by hand:

```bash
php artisan compass:endpoints-check
```

### Client

Install [maarheeze/compass-client-laravel](https://github.com/maarheeze/compass-client-laravel) on a site to report what cannot be seen from outside: debug mode, the environment and the endpoints to monitor. The Forge sync reads it from every site.

Generate the key Compass sends to every client, and use the same key on each site (or a custom per site):

```bash
php artisan compass:key-generate
```

## License

MIT
