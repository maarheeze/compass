<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Tests\Fixtures\CollectedPullRequestFactory;

test('external ids are the external ids of the pull requests', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $collectedPullRequestCollection = new CollectedPullRequestCollection([$collectedPullRequest]);
    $externalIds = $collectedPullRequestCollection->externalIds();

    expect($externalIds->all())
        ->toBe([$collectedPullRequest->externalId]);
});

test('repository names are the repository names of the pull requests', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $collectedPullRequestCollection = new CollectedPullRequestCollection([$collectedPullRequest]);
    $repositoryNames = $collectedPullRequestCollection->repositoryNames();

    expect($repositoryNames->all())
        ->toBe([$collectedPullRequest->repositoryName]);
});
