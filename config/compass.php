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
    | with, and the Forge API token the sites are read with. Neither is ever a
    | signed-in person's token.
    |
    */

    'collector' => [
        'forge_token' => env('COMPASS_FORGE_TOKEN'),
        'github_token' => env('COMPASS_GITHUB_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Forge organization
    |--------------------------------------------------------------------------
    |
    | The slug of the Forge organization whose sites are collected.
    |
    */

    'forge_organization' => env('COMPASS_FORGE_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Forge server pattern
    |--------------------------------------------------------------------------
    |
    | A regular expression on the server name: only the sites of matching
    | servers are collected. The default matches every server.
    |
    */

    'forge_server_pattern' => env('COMPASS_FORGE_SERVER_PATTERN', '/.*/'),

    /*
    |--------------------------------------------------------------------------
    | GitHub organization
    |--------------------------------------------------------------------------
    |
    | The GitHub organization Compass serves: its active members may sign in,
    | and all its non-archived repositories are collected.
    | Membership is checked at sign-in only.
    |
    */

    'github_organization' => env('COMPASS_GITHUB_ORGANIZATION'),

];
