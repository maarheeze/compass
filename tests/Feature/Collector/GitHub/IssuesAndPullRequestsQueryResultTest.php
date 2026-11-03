<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQueryResult;
use Maarheeze\Compass\Tests\Fixtures\CollectedIssueFactory;
use Maarheeze\Compass\Tests\Fixtures\CollectedPullRequestFactory;

test('issues and pull requests query result holds the collected issues it is given', function (): void {
    $collectedIssues = new CollectedIssueCollection([CollectedIssueFactory::make()]);

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(
        $collectedIssues,
        new CollectedPullRequestCollection(),
        fake()->url(),
    );

    expect($issuesAndPullRequestsQueryResult->collectedIssues)
        ->toBe($collectedIssues);
});

test('issues and pull requests query result holds the collected pull requests it is given', function (): void {
    $collectedPullRequests = new CollectedPullRequestCollection([CollectedPullRequestFactory::make()]);

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(
        new CollectedIssueCollection(),
        $collectedPullRequests,
        fake()->url(),
    );

    expect($issuesAndPullRequestsQueryResult->collectedPullRequests)
        ->toBe($collectedPullRequests);
});

test('issues and pull requests query result holds the next page url it is given', function (): void {
    $nextPageUrl = fake()->url();

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(
        new CollectedIssueCollection(),
        new CollectedPullRequestCollection(),
        $nextPageUrl,
    );

    expect($issuesAndPullRequestsQueryResult->nextPageUrl)
        ->toBe($nextPageUrl);
});
