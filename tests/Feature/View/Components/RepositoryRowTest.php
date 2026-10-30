<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\RepositoryRow;

test('render renders the repository row view', function (): void {
    $repository = Repository::factory()->create();

    $repositoryRow = $this->app->make(RepositoryRow::class, ['repository' => $repository]);

    expect($repositoryRow->render()->name())
        ->toBe('compass::components.repository-row');
});

test('url is the compass page of the repository', function (): void {
    $repository = Repository::factory()->create();

    $repositoryRow = $this->app->make(RepositoryRow::class, ['repository' => $repository]);

    expect($repositoryRow->url)
        ->toBe(route(RouteName::GITHUB_REPOSITORY, $repository));
});
