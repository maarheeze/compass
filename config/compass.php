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
    | collected; the default matches every server), and the Forge API token
    | the sites are read with.
    |
    */

    'forge' => [
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

];
