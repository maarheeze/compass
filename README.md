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

COMPASS_FORGE_ORGANIZATION=your-forge-organization
COMPASS_FORGE_SERVER_PATTERN=/^production-/
COMPASS_FORGE_TOKEN=

COMPASS_CLIENT_KEY=
COMPASS_CLIENT_PATH=/_compass
```

- `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` — a GitHub OAuth App of the organization, with `{APP_URL}/auth/github/callback` as callback URL (prefixed with `COMPASS_PATH` when set). Only active members of the organization are let in.
- `COMPASS_GITHUB_TOKEN` — a token with read access to the organization's repositories: a classic token with the `repo` scope, or a fine-grained token with read-only *Contents*, *Metadata*, *Issues* and *Pull requests* permissions.
- `COMPASS_FORGE_TOKEN` — created under **Forge → Account → API Tokens**. The optional server pattern limits the sites to those of matching servers.
- `COMPASS_CLIENT_KEY` and `COMPASS_CLIENT_PATH` — the default key and path for reading [the client](#client); a site can override both on its page.

## Usage

The collector runs on the Laravel scheduler and queue, so both need to run:

```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
php artisan queue:work
```

Both syncs run hourly, and can be started from the dashboard's header or by hand:

```bash
php artisan compass:github-sync
php artisan compass:forge-sync
```

### Client

Install [maarheeze/compass-client-laravel](https://github.com/maarheeze/compass-client-laravel) on a site to report what cannot be seen from outside: debug mode, the environment and the endpoints to monitor. The Forge sync reads it from every site.

Generate the key Compass sends to every client, and use the same key on each site (or a custom per site):

```bash
php artisan compass:key-generate
```

## License

MIT
