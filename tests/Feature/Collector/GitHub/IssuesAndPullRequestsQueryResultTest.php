<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQueryResult;
use Maarheeze\Compass\Tests\Fixtures\GitHubIssueFactory;
use Maarheeze\Compass\Tests\Fixtures\GitHubPullRequestFactory;

test('issues and pull requests query result holds the github issues it is given', function (): void {
    $gitHubIssues = new GitHubIssueCollection([GitHubIssueFactory::make()]);

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult($gitHubIssues, new GitHubPullRequestCollection(), fake()->url());

    expect($issuesAndPullRequestsQueryResult->gitHubIssues)
        ->toBe($gitHubIssues);
});

test('issues and pull requests query result holds the github pull requests it is given', function (): void {
    $gitHubPullRequests = new GitHubPullRequestCollection([GitHubPullRequestFactory::make()]);

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(new GitHubIssueCollection(), $gitHubPullRequests, fake()->url());

    expect($issuesAndPullRequestsQueryResult->gitHubPullRequests)
        ->toBe($gitHubPullRequests);
});

test('issues and pull requests query result holds the next page url it is given', function (): void {
    $nextPageUrl = fake()->url();

    $issuesAndPullRequestsQueryResult = new IssuesAndPullRequestsQueryResult(
        new GitHubIssueCollection(),
        new GitHubPullRequestCollection(),
        $nextPageUrl,
    );

    expect($issuesAndPullRequestsQueryResult->nextPageUrl)
        ->toBe($nextPageUrl);
});
