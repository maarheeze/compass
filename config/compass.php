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
    | Job locks
    |--------------------------------------------------------------------------
    |
    | A collector or task job waits while another job writes the same rows.
    | It tries again after the retry seconds, and the lock of a job whose
    | worker died is freed after the expiry minutes.
    |
    */

    'lock_retry_seconds' => env('COMPASS_LOCK_RETRY_SECONDS', 60),

    'lock_expiry_minutes' => env('COMPASS_LOCK_EXPIRY_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Signing in
    |--------------------------------------------------------------------------
    |
    | Credentials of the GitHub OAuth App people sign in with, and the
    | organization whose active members may sign in, checked at sign-in only.
    | A sign-in is remembered for the remember minutes, after which people
    | sign in again and their membership is checked again.
    |
    */

    'authentication' => [
        'remember_minutes' => env('COMPASS_REMEMBER_MINUTES', 1440),
        'github' => [
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'organization' => env('COMPASS_GITHUB_ORGANIZATION'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | How often the scheduler runs the tasks of Compass's own, which collect
    | from no source: the endpoint checks. A task left out is not scheduled,
    | but still runs when started by hand.
    |
    */

    'schedule' => [
        'site_endpoints' => ScheduleFrequency::EVERY_FIFTEEN_MINUTES,
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
        'compass-client' => [
            'driver' => 'compass-client',
            'key' => env('COMPASS_CLIENT_KEY'),
            'path' => env('COMPASS_CLIENT_PATH', '/_compass'),
            'schedule' => [
                'site_reports' => ScheduleFrequency::HOURLY,
            ],
        ],
    ],

];
