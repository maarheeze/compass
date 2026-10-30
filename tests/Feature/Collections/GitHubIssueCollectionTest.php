<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Tests\Fixtures\GitHubIssueFactory;

test('git hub ids are the ids of the issues', function (): void {
    $gitHubIssue = GitHubIssueFactory::make();

    $gitHubIssueCollection = new GitHubIssueCollection([$gitHubIssue]);
    $gitHubIds = $gitHubIssueCollection->gitHubIds();

    expect($gitHubIds->all())
        ->toBe([$gitHubIssue->gitHubId]);
});

test('repository names are the repository names of the issues', function (): void {
    $gitHubIssue = GitHubIssueFactory::make();

    $gitHubIssueCollection = new GitHubIssueCollection([$gitHubIssue]);
    $repositoryNames = $gitHubIssueCollection->repositoryNames();

    expect($repositoryNames->all())
        ->toBe([$gitHubIssue->repositoryName]);
});
