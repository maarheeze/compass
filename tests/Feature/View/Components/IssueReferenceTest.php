<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\IssueReference;

test('url is the compass page of the repository', function (): void {
    $repository = Repository::factory()->create();

    $issueReference = $this->app->make(IssueReference::class, [
        'externalReference' => sprintf('#%d', fake()->numberBetween(1, 9999)),
        'repository' => $repository,
    ]);

    expect($issueReference->url)
        ->toBe(route(RouteName::REPOSITORY, $repository));
});

test('url is empty without a repository', function (): void {
    $issueReference = $this->app->make(IssueReference::class, [
        'externalReference' => sprintf('#%d', fake()->numberBetween(1, 9999)),
        'repository' => null,
    ]);

    expect($issueReference->url)
        ->toBeNull();
});
