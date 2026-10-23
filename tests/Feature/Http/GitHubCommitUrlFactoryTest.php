<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\GitHubCommitUrlFactory;
use Maarheeze\Compass\Models\Repository;

test('create builds the github page of the commit in the repository', function (): void {
    $repository = Repository::factory()->create();
    $commitHash = fake()->sha1();

    $gitHubCommitUrlFactory = $this->app->make(GitHubCommitUrlFactory::class);
    $gitHubCommitUrl = $gitHubCommitUrlFactory->create($repository, $commitHash);

    expect($gitHubCommitUrl)
        ->toBe(sprintf('%s/commit/%s', $repository->url, $commitHash));
});

test('create comparison builds the github comparison from the base commit to the last commit of the repository', function (): void {
    $repository = Repository::factory()->create();
    $baseCommitHash = fake()->sha1();

    $gitHubCommitUrlFactory = $this->app->make(GitHubCommitUrlFactory::class);
    $gitHubComparisonUrl = $gitHubCommitUrlFactory->createComparison($repository, $baseCommitHash);

    expect($gitHubComparisonUrl)
        ->toBe(sprintf('%s/compare/%s...%s', $repository->url, $baseCommitHash, $repository->last_commit_sha));
});
