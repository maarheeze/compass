<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\ScheduleFrequency;

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
    | a collector starts and ends, a debug line per page, composer file,
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
    | GitHub
    |--------------------------------------------------------------------------
    |
    | Credentials of the GitHub OAuth App people sign in with, and the
    | organization whose active members may sign in, checked at sign-in only.
    |
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'organization' => env('COMPASS_GITHUB_ORGANIZATION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sources
    |--------------------------------------------------------------------------
    |
    | The sources Compass collects from, by name: each names its driver and
    | the settings that driver needs, and under "schedule" how often the
    | scheduler runs each collector. A collector left out is not
    | scheduled, but still runs when started by hand.
    |
    */

    'sources' => [
        'github' => [
            'driver' => 'github',
            'organization' => env('COMPASS_GITHUB_ORGANIZATION'),
            'token' => env('COMPASS_GITHUB_TOKEN'),
            'schedule' => [
                'repositories' => ScheduleFrequency::HOURLY,
                'issues' => ScheduleFrequency::HOURLY,
                'pull_requests' => ScheduleFrequency::HOURLY,
            ],
        ],
        'forge' => [
            'driver' => 'forge',
            'organization' => env('COMPASS_FORGE_ORGANIZATION'),
            'token' => env('COMPASS_FORGE_TOKEN'),
            'auto_deploy_grace_minutes' => env('COMPASS_FORGE_AUTO_DEPLOY_GRACE_MINUTES', 5),
            'schedule' => [
                'sites' => ScheduleFrequency::HOURLY,
            ],
        ],
    ],

];
