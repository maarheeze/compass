# maarheeze/compass

**A self-hosted status dashboard for your repositories and the sites they deploy to**

Compass answers, in one place, what is otherwise a round of clicking through every repository and every server: what the latest commit is, what is actually deployed, whether that deployment succeeded, what a project depends on, which issues and pull requests are open, and which sites are up. It collects this on a schedule, raises an alert for what needs attention, and lets the team assign, mute and close those alerts. Members of the organization sign in with GitHub.

Compass is read-only: it never changes anything at its sources.

## Installation

```bash
composer require maarheeze/compass
php artisan vendor:publish --tag=compass-config
php artisan migrate
```

Compass runs on the Laravel scheduler and queue. Everything else is set in `config/compass.php`.

## Collectors

Compass collects from the sources listed in its config. The collectors below are included, and each is optional: one runs only when a source using it is listed.

### GitHub

Collects the repositories of an organization, with their latest commit, composer and Dependabot files, open issues and pull requests. Alerts on composer setups that need a look, missing Dependabot coverage and repositories that disappeared.

### Laravel Forge

Collects the sites of a Forge organization, with what is deployed and how that deployment went. Alerts on sites that are behind their repository, deployments that are overdue and sites that disappeared.

### Compass client

Install [maarheeze/compass-client-laravel](https://github.com/maarheeze/compass-client-laravel) on a site to report what cannot be seen from outside: debug mode, the environment and the endpoints to monitor. Compass checks those endpoints, and alerts on a site in debug mode, a site not in production, an endpoint that is down, a client that refuses Compass's key and a client too outdated to report.

## License

MIT
