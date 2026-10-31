<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | The URI prefix the dashboard is served under. "/" serves it from the
    | root of the application.
    |
    */

    'path' => env('COMPASS_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Log channel
    |--------------------------------------------------------------------------
    |
    | The log channel the collector writes what it did to: an info line when
    | a collection starts and ends, a debug line per page, composer file,
    | site and endpoint.
    | Null writes to the default channel of the application.
    |
    */

    'log_channel' => env('COMPASS_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Display timezone
    |--------------------------------------------------------------------------
    |
    | The timezone dates and times are shown in.
    |
    */

    'display_timezone' => env('COMPASS_DISPLAY_TIMEZONE', 'Europe/Amsterdam'),

    /*
    |--------------------------------------------------------------------------
    | Display date and time format
    |--------------------------------------------------------------------------
    |
    | The PHP date format dates and times are shown in.
    |
    */

    'display_date_time_format' => env('COMPASS_DISPLAY_DATE_TIME_FORMAT', 'Y-m-d H:i:s'),

    /*
    |--------------------------------------------------------------------------
    | Items per page
    |--------------------------------------------------------------------------
    |
    | The number of items a paginated list shows per page.
    |
    */

    'items_per_page' => env('COMPASS_ITEMS_PER_PAGE', 10),

    /*
    |--------------------------------------------------------------------------
    | Client
    |--------------------------------------------------------------------------
    |
    | The key Compass sends to the maarheeze/compass-client-laravel package
    | on the sites (the same key on every site), and the path that package
    | answers on, unless a site overrides it.
    |
    */

    'client' => [
        'key' => env('COMPASS_CLIENT_KEY'),
        'path' => env('COMPASS_CLIENT_PATH', '/_compass'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Forge
    |--------------------------------------------------------------------------
    |
    | The slug of the Forge organization whose sites are collected, a regular
    | expression on the server name (only the sites of matching servers are
    | collected; the default matches every server), the Forge API token the
    | sites are read with, and the minutes a site with auto-deploy enabled may
    | take to deploy the last commit before its deployment is overdue.
    |
    */

    'forge' => [
        'auto_deploy_grace_minutes' => env('COMPASS_FORGE_AUTO_DEPLOY_GRACE_MINUTES', 5),
        'organization' => env('COMPASS_FORGE_ORGANIZATION'),
        'server_pattern' => env('COMPASS_FORGE_SERVER_PATTERN', '/.*/'),
        'token' => env('COMPASS_FORGE_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub
    |--------------------------------------------------------------------------
    |
    | Credentials of the GitHub OAuth App people sign in with, the
    | organization Compass serves (its active members may sign in, checked at
    | sign-in only, and all its non-archived repositories are collected), and
    | the token of the CI user the repositories are read with. That token is
    | never a signed-in person's token.
    |
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'organization' => env('COMPASS_GITHUB_ORGANIZATION'),
        'token' => env('COMPASS_GITHUB_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | Whether the scheduler runs the Forge sync, the GitHub sync and the
    | endpoint checks. A task switched off still runs when started by hand
    | or from the dashboard.
    |
    */

    'schedule' => [
        'forge' => env('COMPASS_SCHEDULE_FORGE', true),
        'github' => env('COMPASS_SCHEDULE_GITHUB', true),
        'site_endpoints' => env('COMPASS_SCHEDULE_SITE_ENDPOINTS', true),
    ],

];
