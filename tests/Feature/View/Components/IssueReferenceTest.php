<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\IssueReference;

test('reference is the full name of the repository and the number', function (): void {
    $repository = Repository::factory()->create();
    $number = fake()->numberBetween(1, 9999);

    $issueReference = $this->app->make(IssueReference::class, [
        'number' => $number,
        'repository' => $repository,
    ]);

    expect($issueReference->reference)
        ->toBe(sprintf('%s#%d', $repository->full_name, $number));
});

test('reference is the number alone without a repository', function (): void {
    $number = fake()->numberBetween(1, 9999);

    $issueReference = $this->app->make(IssueReference::class, [
        'number' => $number,
        'repository' => null,
    ]);

    expect($issueReference->reference)
        ->toBe(sprintf('#%d', $number));
});

test('url is the compass page of the repository', function (): void {
    $repository = Repository::factory()->create();

    $issueReference = $this->app->make(IssueReference::class, [
        'number' => fake()->numberBetween(1, 9999),
        'repository' => $repository,
    ]);

    expect($issueReference->url)
        ->toBe(route(RouteName::GITHUB_REPOSITORY, $repository));
});

test('url is empty without a repository', function (): void {
    $issueReference = $this->app->make(IssueReference::class, [
        'number' => fake()->numberBetween(1, 9999),
        'repository' => null,
    ]);

    expect($issueReference->url)
        ->toBeNull();
});
