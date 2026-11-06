<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Tests\Fixtures\CollectedIssueFactory;

test('external ids are the external ids of the issues', function (): void {
    $collectedIssue = CollectedIssueFactory::make();

    $collectedIssueCollection = new CollectedIssueCollection([$collectedIssue]);
    $externalIds = $collectedIssueCollection->externalIds();

    expect($externalIds->all())
        ->toBe([$collectedIssue->externalId]);
});

test('repository lookup keys are the repository lookup keys of the issues', function (): void {
    $collectedIssue = CollectedIssueFactory::make();

    $collectedIssueCollection = new CollectedIssueCollection([$collectedIssue]);
    $repositoryLookupKeys = $collectedIssueCollection->repositoryLookupKeys();

    expect($repositoryLookupKeys->all())
        ->toBe([$collectedIssue->repositoryLookupKey]);
});
