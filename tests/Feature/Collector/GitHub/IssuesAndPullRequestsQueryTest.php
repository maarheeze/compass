<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQuery;
use Maarheeze\Compass\Tests\Fixtures\CollectedIssueFactory;
use Maarheeze\Compass\Tests\Fixtures\CollectedPullRequestFactory;

test('issues search the open issues of the unarchived repositories, oldest first', function (): void {
    $organization = fake()->slug();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($http, fake()->sha1()),
        $organization,
    );
    iterator_to_array($issuesAndPullRequestsQuery->issues());

    $http->assertSent(static function (HttpRequest $request) use ($organization): bool {
        return str_starts_with($request->url(), 'https://api.github.com/search/issues?')
            && $request['q'] === sprintf('org:%s is:issue is:open archived:false', $organization)
            && $request['sort'] === 'created'
            && $request['order'] === 'asc'
            && $request['per_page'] === '100';
    });
});

test('pull requests search the open pull requests of the unarchived repositories, oldest first', function (): void {
    $organization = fake()->slug();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($http, fake()->sha1()),
        $organization,
    );
    iterator_to_array($issuesAndPullRequestsQuery->pullRequests());

    $http->assertSent(static function (HttpRequest $request) use ($organization): bool {
        return str_starts_with($request->url(), 'https://api.github.com/search/issues?')
            && $request['q'] === sprintf('org:%s is:pr is:open archived:false', $organization)
            && $request['sort'] === 'created'
            && $request['order'] === 'asc'
            && $request['per_page'] === '100';
    });
});

test('issues continue a full page from the creation date of its last issue', function (): void {
    $itemResponses = [];

    for ($itemIndex = 0; $itemIndex < 100; $itemIndex++) {
        $itemResponses[] = [
            'node_id' => fake()->unique()->sha1(),
            'repository_url' => fake()->url(),
            'number' => fake()->numberBetween(1, 9999),
            'title' => fake()->sentence(),
            'html_url' => fake()->url(),
            'user' => null,
            'created_at' => fake()->iso8601(),
            'assignees' => [],
        ];
    }

    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push(['incomplete_results' => false, 'items' => $itemResponses]);
    $responseSequence->push(['incomplete_results' => false, 'items' => []]);
    $http->fake(['*' => $responseSequence]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );
    iterator_to_array($issuesAndPullRequestsQuery->issues());

    $lastCreatedAt = $itemResponses[99]['created_at'];
    $http->assertSent(static function (HttpRequest $request) use ($lastCreatedAt): bool {
        return str_contains($request['q'], sprintf('created:>=%s', $lastCreatedAt));
    });
});

test('issues leave out the issues already collected at the date they continue from', function (): void {
    $lastCreatedAt = fake()->iso8601();
    $itemResponses = [];

    for ($itemIndex = 0; $itemIndex < 100; $itemIndex++) {
        $itemResponses[] = [
            'node_id' => fake()->unique()->sha1(),
            'repository_url' => fake()->url(),
            'number' => fake()->numberBetween(1, 9999),
            'title' => fake()->sentence(),
            'html_url' => fake()->url(),
            'user' => null,
            'created_at' => $lastCreatedAt,
            'assignees' => [],
        ];
    }

    $unseenItemResponse = [
        'node_id' => fake()->unique()->sha1(),
        'repository_url' => fake()->url(),
        'number' => fake()->numberBetween(1, 9999),
        'title' => fake()->sentence(),
        'html_url' => fake()->url(),
        'user' => null,
        'created_at' => $lastCreatedAt,
        'assignees' => [],
    ];

    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push(['incomplete_results' => false, 'items' => $itemResponses]);
    $responseSequence->push(['incomplete_results' => false, 'items' => [$itemResponses[99], $unseenItemResponse]]);
    $http->fake(['*' => $responseSequence]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );
    $collectedIssuesPerPage = iterator_to_array($issuesAndPullRequestsQuery->issues());

    expect($collectedIssuesPerPage[1]->sole()->externalId)
        ->toBe($unseenItemResponse['node_id']);
});

test('issues end with a page that is not full', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($http, fake()->sha1()),
        fake()->slug(),
    );
    iterator_to_array($issuesAndPullRequestsQuery->issues());

    $http->assertSentCount(1);
});

test('issues hold the collected issues in the response', function (): void {
    $collectedIssue = CollectedIssueFactory::make();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'incomplete_results' => false,
            'items' => [
                [
                    'node_id' => $collectedIssue->externalId,
                    'repository_url' => sprintf(
                        'https://api.github.com/repos/%s',
                        $collectedIssue->repositoryLookupKey,
                    ),
                    'number' => $collectedIssue->number,
                    'title' => $collectedIssue->title,
                    'html_url' => $collectedIssue->url,
                    'user' => [
                        'login' => $collectedIssue->authorLogin,
                    ],
                    'created_at' => $collectedIssue->openedAt->toIso8601String(),
                    'assignees' => [
                        [
                            'login' => $collectedIssue->assigneeLogins[0],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $collectedIssues = $issuesAndPullRequestsQuery->issues()->current();

    expect($collectedIssues)
        ->toEqual(new CollectedIssueCollection([$collectedIssue]));
});

test('pull requests hold the collected pull requests in the response', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'incomplete_results' => false,
            'items' => [
                [
                    'node_id' => $collectedPullRequest->externalId,
                    'repository_url' => sprintf(
                        'https://api.github.com/repos/%s',
                        $collectedPullRequest->repositoryLookupKey,
                    ),
                    'number' => $collectedPullRequest->number,
                    'title' => $collectedPullRequest->title,
                    'html_url' => $collectedPullRequest->url,
                    'user' => [
                        'login' => $collectedPullRequest->authorLogin,
                    ],
                    'draft' => $collectedPullRequest->isDraft,
                    'created_at' => $collectedPullRequest->openedAt->toIso8601String(),
                    'assignees' => [
                        [
                            'login' => $collectedPullRequest->assigneeLogins[0],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $collectedPullRequests = $issuesAndPullRequestsQuery->pullRequests()->current();

    expect($collectedPullRequests)
        ->toEqual(new CollectedPullRequestCollection([$collectedPullRequest]));
});

test('issues hold no author login for an issue without a user', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'incomplete_results' => false,
            'items' => [
                [
                    'node_id' => fake()->sha1(),
                    'repository_url' => fake()->url(),
                    'number' => fake()->numberBetween(1, 9999),
                    'title' => fake()->sentence(),
                    'html_url' => fake()->url(),
                    'user' => null,
                    'created_at' => fake()->iso8601(),
                    'assignees' => [],
                ],
            ],
        ]),
    ]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $collectedIssues = $issuesAndPullRequestsQuery->issues()->current();

    expect($collectedIssues->sole()->authorLogin)
        ->toBeNull();
});

test('issues throw when github could not search everything', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response(['incomplete_results' => true, 'items' => []]),
    ]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $issuesAndPullRequestsQuery->issues()->current();
})->throws(GitHubQueryFailed::class);

test('issues throw when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $issuesAndPullRequestsQuery->issues()->current();
})->throws(GitHubQueryFailed::class);
