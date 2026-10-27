<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\RepositoryLink;

test('url is the compass page of the repository', function (): void {
    $repository = Repository::factory()->create();

    $repositoryLink = $this->app->make(RepositoryLink::class, ['repository' => $repository]);

    expect($repositoryLink->url)
        ->toBe(route(RouteName::GITHUB_REPOSITORY, $repository));
});
