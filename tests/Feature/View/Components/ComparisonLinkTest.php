<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\GitHubCommitUrlFactory;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\ComparisonLink;

test('url is the github comparison from the base commit to the last commit of the repository', function (): void {
    $repository = Repository::factory()->create();
    $baseCommitHash = fake()->sha1();
    $gitHubCommitUrlFactory = $this->app->make(GitHubCommitUrlFactory::class);

    $comparisonLink = $this->app->make(ComparisonLink::class, ['repository' => $repository, 'baseCommitHash' => $baseCommitHash]);

    expect($comparisonLink->url)
        ->toBe($gitHubCommitUrlFactory->createComparison($repository, $baseCommitHash));
});
