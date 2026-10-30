<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\IssueReference;

test('reference is the organization, the repository and the number', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.github.organization', $organization);
    $repository = Repository::factory()->create();
    $number = fake()->numberBetween(1, 9999);

    $issueReference = $this->app->make(IssueReference::class, [
        'repository' => $repository,
        'number' => $number,
    ]);

    expect($issueReference->reference)
        ->toBe(sprintf('%s/%s#%d', $organization, $repository->name, $number));
});

test('url is the compass page of the repository', function (): void {
    $repository = Repository::factory()->create();

    $issueReference = $this->app->make(IssueReference::class, [
        'repository' => $repository,
        'number' => fake()->numberBetween(1, 9999),
    ]);

    expect($issueReference->url)
        ->toBe(route(RouteName::GITHUB_REPOSITORY, $repository));
});
