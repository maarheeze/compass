<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
use Maarheeze\Compass\Tests\Fixtures\GitHubPullRequestFactory;

test('git hub ids are the ids of the pull requests', function (): void {
    $gitHubPullRequest = GitHubPullRequestFactory::make();

    $gitHubPullRequestCollection = new GitHubPullRequestCollection([$gitHubPullRequest]);
    $gitHubIds = $gitHubPullRequestCollection->gitHubIds();

    expect($gitHubIds->all())
        ->toBe([$gitHubPullRequest->gitHubId]);
});

test('repository names are the repository names of the pull requests', function (): void {
    $gitHubPullRequest = GitHubPullRequestFactory::make();

    $gitHubPullRequestCollection = new GitHubPullRequestCollection([$gitHubPullRequest]);
    $repositoryNames = $gitHubPullRequestCollection->repositoryNames();

    expect($repositoryNames->all())
        ->toBe([$gitHubPullRequest->repositoryName]);
});
