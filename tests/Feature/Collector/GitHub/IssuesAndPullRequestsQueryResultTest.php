<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQueryResult;
use Maarheeze\Compass\Tests\Fixtures\CollectedIssueFactory;
use Maarheeze\Compass\Tests\Fixtures\GitHubPullRequestFactory;

test('issues and pull requests query result holds the collected issues it is given', function (): void {
    $collectedIssues = new CollectedIssueCollection([CollectedIssueFactory::make()]);

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(
        $collectedIssues,
        new GitHubPullRequestCollection(),
        fake()->url(),
    );

    expect($issuesAndPullRequestsQueryResult->collectedIssues)
        ->toBe($collectedIssues);
});

test('issues and pull requests query result holds the github pull requests it is given', function (): void {
    $gitHubPullRequests = new GitHubPullRequestCollection([GitHubPullRequestFactory::make()]);

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(new CollectedIssueCollection(), $gitHubPullRequests, fake()->url());

    expect($issuesAndPullRequestsQueryResult->gitHubPullRequests)
        ->toBe($gitHubPullRequests);
});

test('issues and pull requests query result holds the next page url it is given', function (): void {
    $nextPageUrl = fake()->url();

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(
        new CollectedIssueCollection(),
        new GitHubPullRequestCollection(),
        $nextPageUrl,
    );

    expect($issuesAndPullRequestsQueryResult->nextPageUrl)
        ->toBe($nextPageUrl);
});
