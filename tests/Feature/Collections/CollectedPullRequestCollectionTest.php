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

test('repository lookup keys are the repository lookup keys of the pull requests', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $collectedPullRequestCollection = new CollectedPullRequestCollection([$collectedPullRequest]);
    $repositoryLookupKeys = $collectedPullRequestCollection->repositoryLookupKeys();

    expect($repositoryLookupKeys->all())
        ->toBe([$collectedPullRequest->repositoryLookupKey]);
});
