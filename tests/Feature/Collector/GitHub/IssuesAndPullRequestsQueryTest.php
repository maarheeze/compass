<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQuery;
use Maarheeze\Compass\Tests\Fixtures\GitHubIssueFactory;
use Maarheeze\Compass\Tests\Fixtures\GitHubPullRequestFactory;

test('page queries the first page of the open issues of the organization', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.github.organization', $organization);

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQuery->page(null);

    $http->assertSent(static function (HttpRequest $request) use ($organization): bool {
        return $request->url() === sprintf(
            'https://api.github.com/orgs/%s/issues?filter=all&state=open&per_page=100',
            $organization,
        );
    });
});

test('page queries the page url it is given', function (): void {
    $pageUrl = fake()->url();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQuery->page($pageUrl);

    $http->assertSent(static function (HttpRequest $request) use ($pageUrl): bool {
        return $request->url() === $pageUrl;
    });
});

test('page holds the github issues in the response', function (): void {
    $gitHubIssue = GitHubIssueFactory::make();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            [
                'node_id' => $gitHubIssue->gitHubId,
                'repository' => [
                    'name' => $gitHubIssue->repositoryName,
                ],
                'number' => $gitHubIssue->number,
                'title' => $gitHubIssue->title,
                'html_url' => $gitHubIssue->url,
                'user' => [
                    'login' => $gitHubIssue->authorLogin,
                ],
                'created_at' => $gitHubIssue->openedAt->toIso8601String(),
                'assignees' => [
                    [
                        'login' => $gitHubIssue->assigneeLogins[0],
                    ],
                ],
            ],
        ]),
    ]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQueryResult = $issuesAndPullRequestsQuery->page(null);

    expect($issuesAndPullRequestsQueryResult->gitHubIssues)
        ->toEqual(new GitHubIssueCollection([$gitHubIssue]));
});

test('page holds the github pull requests in the response', function (): void {
    $gitHubPullRequest = GitHubPullRequestFactory::make();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            [
                'node_id' => $gitHubPullRequest->gitHubId,
                'repository' => [
                    'name' => $gitHubPullRequest->repositoryName,
                ],
                'number' => $gitHubPullRequest->number,
                'title' => $gitHubPullRequest->title,
                'html_url' => $gitHubPullRequest->url,
                'user' => [
                    'login' => $gitHubPullRequest->authorLogin,
                ],
                'draft' => $gitHubPullRequest->isDraft,
                'created_at' => $gitHubPullRequest->openedAt->toIso8601String(),
                'assignees' => [
                    [
                        'login' => $gitHubPullRequest->assigneeLogins[0],
                    ],
                ],
                'pull_request' => [
                    'url' => fake()->url(),
                ],
            ],
        ]),
    ]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQueryResult = $issuesAndPullRequestsQuery->page(null);

    expect($issuesAndPullRequestsQueryResult->gitHubPullRequests)
        ->toEqual(new GitHubPullRequestCollection([$gitHubPullRequest]));
});

test('page holds no author login for an issue without a user', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            [
                'node_id' => fake()->sha1(),
                'repository' => [
                    'name' => fake()->slug(),
                ],
                'number' => fake()->numberBetween(1, 9999),
                'title' => fake()->sentence(),
                'html_url' => fake()->url(),
                'user' => null,
                'created_at' => fake()->iso8601(),
                'assignees' => [],
            ],
        ]),
    ]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQueryResult = $issuesAndPullRequestsQuery->page(null);

    expect($issuesAndPullRequestsQueryResult->gitHubIssues->sole()->authorLogin)
        ->toBeNull();
});

test('page holds the next link as next page url when there is a next page', function (): void {
    $nextPageUrl = fake()->url();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([], 200, [
            'Link' => sprintf('<%s>; rel="next", <%s>; rel="last"', $nextPageUrl, fake()->url()),
        ]),
    ]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQueryResult = $issuesAndPullRequestsQuery->page(null);

    expect($issuesAndPullRequestsQueryResult->nextPageUrl)
        ->toBe($nextPageUrl);
});

test('page holds no next page url on the last page', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([], 200, [
            'Link' => sprintf('<%s>; rel="first", <%s>; rel="prev"', fake()->url(), fake()->url()),
        ]),
    ]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQueryResult = $issuesAndPullRequestsQuery->page(null);

    expect($issuesAndPullRequestsQueryResult->nextPageUrl)
        ->toBeNull();
});

test('page throws when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $issuesAndPullRequestsQuery = $this->app->make(IssuesAndPullRequestsQuery::class);
    $issuesAndPullRequestsQuery->page(null);
})->throws(GitHubQueryFailed::class);
