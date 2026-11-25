# maarheeze/compass

**A self-hosted status dashboard for your repositories and the sites they deploy to**

Compass answers, in one place, what is otherwise a round of clicking through every repository and every server: what the latest commit is, what is actually deployed, whether that deployment succeeded, what a project depends on, which issues and pull requests are open, and which sites are up. It collects this on a schedule, raises an alert for what needs attention, and lets the team assign, mute and close those alerts. Members of the organisation sign in with GitHub.

Compass is read-only: it never changes anything at its sources.

## Installation

```bash
composer require maarheeze/compass
php artisan vendor:publish --tag=compass-config
php artisan migrate
```

Compass runs on the Laravel scheduler and queue. Everything else is set in `config/compass.php`.

A run someone starts by hand, or a site asks for, should not wait behind the scheduled runs. Set a high and a low queue, and start the worker with the high queue before the default queue and the low queue after it:

```dotenv
COMPASS_HIGH_QUEUE=high
COMPASS_LOW_QUEUE=low
```

```bash
php artisan queue:work --queue=high,default,low
```

Without them, every run goes on the default queue.

Upgrading from an earlier major version: see [UPGRADE.md](UPGRADE.md) for what changed and what to do.

Import Compass's JavaScript in the host's `resources/js/app.js`, after Echo if the host has it:

```js
import '../../vendor/maarheeze/compass/resources/js/compass.js';
```

The sites Compass monitors can install [maarheeze/compass-client-laravel](https://github.com/maarheeze/compass-client-laravel) to report their debug mode, environment, maintenance mode and endpoints; see [Compass client](#compass-client).

## Signing in

People sign in with their GitHub account. Register an OAuth App under the organisation's developer settings on GitHub, with as its callback URL the host's address followed by `/auth/github/callback`, after `COMPASS_PATH` when Compass is not served from the root:

```
https://compass.example.com/auth/github/callback
```

Then set its credentials and the organisation whose members may sign in:

```dotenv
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
COMPASS_GITHUB_ORGANISATION=
```

Only active members of that organisation get in, and only with a verified email address on their GitHub account. Compass asks for the `read:org` and `user:email` scopes; an organisation that restricts OAuth App access has to approve the app first. Membership is checked when signing in, and a sign-in lasts `COMPASS_REMEMBER_MINUTES`, a day by default.

## Live updates

With broadcasting set up on the host, Compass pages update themselves while open and show runs and alerts in toasts as they happen:

```bash
php artisan install:broadcasting
```

This brings Reverb and Echo; keep the Reverb server running next to the queue. The broadcasting auth route must stay behind the `web` middleware only, as `install:broadcasting` sets it: Compass signs its users in on a guard of its own, which the host's `auth` middleware refuses.

A toast waits for the jobs queued before it. To have even a single worker take Compass's broadcasts first, set `COMPASS_BROADCAST_QUEUE=broadcasts` and start the worker with `--queue=broadcasts,default`, or `--queue=broadcasts,high,default,low` with the high and low queues.

Without broadcasting, Compass works the same without live updates. Its events still queue broadcast jobs, which the `null` broadcaster drops.

## Brand

The header and the sign-in page show the application name (`APP_NAME`). To show a logo instead, override the brand view in `resources/views/vendor/compass/components/brand.blade.php`:

```blade
<img src="{{ asset('images/logo.svg') }}" alt="{{ $applicationName }}" class="h-6">
```

## Collectors

Compass collects from the sources listed under `sources` in `config/compass.php`, which ships empty. The collectors below are included, and each is optional: one runs only when a source using it is listed. Each comes with a full example of its source.

### GitHub

Collects the repositories of an organisation, with their latest commit, composer and Dependabot files, open issues and pull requests. Alerts on composer setups that need a look, missing Dependabot coverage and repositories that disappeared.

```php
'sources' => [
    'github' => [
        'driver' => 'github',
        'organisation' => env('COMPASS_GITHUB_ORGANISATION'),
        'token' => env('COMPASS_GITHUB_TOKEN'),
        'requests_per_minute' => env('COMPASS_GITHUB_REQUESTS_PER_MINUTE', 60),
        'issue_types' => [
            'Bug' => IssueType::BUG,
            'Feature' => IssueType::FEATURE,
            'Task' => IssueType::TASK,
        ],
        'schedule' => [
            'repositories' => ScheduleFrequency::HOURLY,
            'issues' => ScheduleFrequency::HOURLY,
            'pull_requests' => ScheduleFrequency::HOURLY,
        ],
    ],
],
```

`issue_types` maps the names of the organisation's issue types onto Compass's types: `IssueType::BUG`, `IssueType::FEATURE` and `IssueType::TASK`. An issue whose type is not listed, or that has no type, has no type in Compass. Left out, no issue has a type.

`requests_per_minute` caps the requests Compass sends to GitHub a minute, REST and GraphQL together. A job that would go over waits until the minute has room, and so does a job GitHub answers with its rate limit, for as long as GitHub asks.

### Laravel Forge

Collects the sites of a Forge organisation, with what is deployed and how that deployment went, and their domains. Alerts on sites that are behind their repository, deployments that are overdue and sites that disappeared.

The token needs the scopes `server:view`, for the sites, and `site:meta`, for their domains.

```php
'sources' => [
    'forge' => [
        'driver' => 'forge',
        'organisation' => env('COMPASS_FORGE_ORGANISATION'),
        'token' => env('COMPASS_FORGE_TOKEN'),
        'requests_per_minute' => env('COMPASS_FORGE_REQUESTS_PER_MINUTE', 60),
        'auto_deploy_grace_minutes' => env('COMPASS_FORGE_AUTO_DEPLOY_GRACE_MINUTES', 5),
        'server_pattern' => env('COMPASS_FORGE_SERVER_PATTERN', '/.*/'),
        'schedule' => [
            'sites' => ScheduleFrequency::HOURLY,
        ],
    ],
],
```

`requests_per_minute` caps the requests Compass sends to Forge a minute. A job that would go over waits until the minute has room, and so does a job Forge answers with too many requests, for as long as Forge asks.

### Compass client

Install [maarheeze/compass-client-laravel](https://github.com/maarheeze/compass-client-laravel) on a site to report what cannot be seen from outside: debug mode, the environment, maintenance mode and the endpoints to monitor. Compass checks those endpoints, and alerts on a site in debug mode, a site not in production, a site reachable on a Forge domain outside maintenance mode, an endpoint that is down, a client that refuses Compass's key and a client too outdated to report.

```php
'sources' => [
    'compass-client' => [
        'driver' => 'compass-client',
        'path' => env('COMPASS_CLIENT_PATH', '/_compass'),
        'schedule' => [
            'site_reports' => ScheduleFrequency::HOURLY,
        ],
    ],
],
```

## Licence

MIT
