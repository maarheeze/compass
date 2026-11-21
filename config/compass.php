<?php

declare(strict_types=1);

use Webmozart\Assert\Assert;

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
    | Broadcast queue
    |--------------------------------------------------------------------------
    |
    | The queue the live updates are broadcast from. A worker that takes
    | this queue first, as in --queue=broadcasts,default, shows a toast as
    | soon as the job it is running is done.
    | Null broadcasts from the default queue of the connection.
    |
    */

    'broadcast_queue' => env('COMPASS_BROADCAST_QUEUE'),

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

    'items_per_page' => (int) Assert::integerish(env('COMPASS_ITEMS_PER_PAGE', 10)),

    /*
    |--------------------------------------------------------------------------
    | Issue type order
    |--------------------------------------------------------------------------
    |
    | The issue types the issue lists show first, in this order, such as
    | [IssueType::BUG, IssueType::FEATURE]. Every other issue follows,
    | typed or not. Within each, the oldest opened issue comes first.
    |
    */

    'issue_type_order' => [],

    /*
    |--------------------------------------------------------------------------
    | Dashboard packages
    |--------------------------------------------------------------------------
    |
    | The packages whose locked versions the dashboard shows, each version
    | with the number of projects locking it.
    |
    */

    'dashboard_packages' => [],

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

    'lock_retry_seconds' => (int) Assert::integerish(env('COMPASS_LOCK_RETRY_SECONDS', 60)),

    'lock_expiry_minutes' => (int) Assert::integerish(env('COMPASS_LOCK_EXPIRY_MINUTES', 15)),

    /*
    |--------------------------------------------------------------------------
    | Inspection requests
    |--------------------------------------------------------------------------
    |
    | The number of requests a minute the sites may send from one IP address
    | to ask to be inspected again. Several sites on one server share its
    | IP address, so this stays well above what one server sends at once.
    |
    */

    'inspection_requests_per_minute' => (int) Assert::integerish(env('COMPASS_INSPECTION_REQUESTS_PER_MINUTE', 60)),

    /*
    |--------------------------------------------------------------------------
    | Signing in
    |--------------------------------------------------------------------------
    |
    | Credentials of the GitHub OAuth App people sign in with, and the
    | organisation whose active members may sign in, checked at sign-in only.
    | A sign-in is remembered for the remember minutes, after which people
    | sign in again and their membership is checked again.
    |
    */

    'authentication' => [
        'remember_minutes' => (int) Assert::integerish(env('COMPASS_REMEMBER_MINUTES', 1440)),
        'github' => [
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'organisation' => env('COMPASS_GITHUB_ORGANISATION'),
        ],
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

    'sources' => [],

];
