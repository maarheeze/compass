<?php

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

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | GitHub OAuth App
    |--------------------------------------------------------------------------
    |
    | Credentials of the OAuth App people sign in with.
    |
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
    ],

];
