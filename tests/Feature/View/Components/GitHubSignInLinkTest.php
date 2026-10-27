<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\GitHubSignInLink;

test('url is the github sign in redirect', function (): void {
    $gitHubSignInLink = $this->app->make(GitHubSignInLink::class);

    expect($gitHubSignInLink->url)
        ->toBe(route(RouteName::GITHUB_REDIRECT));
});
