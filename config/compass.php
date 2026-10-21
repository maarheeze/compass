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
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Credentials of the GitHub OAuth App people sign in with.
    |
    */

    'authentication' => [
        'github' => [
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Collector
    |--------------------------------------------------------------------------
    |
    | Token of the CI user the collector reads the organization's repositories
    | with. It is never a signed-in person's token.
    |
    */

    'collector' => [
        'github_token' => env('COMPASS_GITHUB_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    |
    | The GitHub organization Compass serves: its active members may sign in,
    | and the repositories of the configured projects are read from it.
    | Membership is checked at sign-in only.
    |
    */

    'organization' => env('COMPASS_ORGANISATION'),

];
