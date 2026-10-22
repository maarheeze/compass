<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssuesAndPullRequests;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\Repository;

test('handle queries its page url', function (): void {
    $pageUrl = fake()->url();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(CollectionRunId::generate(), $pageUrl);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($pageUrl): bool {
        return $request->url() === $pageUrl;
    });
});

test('handle stores the issues of the page', function (): void {
    $repository = Repository::factory()->create();
    $issueResponse = [
        'node_id' => fake()->sha1(),
        'repository' => [
            'name' => $repository->name,
        ],
        'number' => fake()->numberBetween(1, 9999),
        'title' => fake()->sentence(),
        'html_url' => fake()->url(),
        'user' => null,
        'created_at' => fake()->iso8601(),
        'assignees' => [],
    ];
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$issueResponse])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests($collectionRunId, null);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertDatabaseHas(Issue::class, [
        'github_id' => $issueResponse['node_id'],
        'collection_run_id' => $collectionRunId,
    ]);
});

test('handle stores the pull requests of the page', function (): void {
    $repository = Repository::factory()->create();
    $pullRequestResponse = [
        'node_id' => fake()->sha1(),
        'repository' => [
            'name' => $repository->name,
        ],
        'number' => fake()->numberBetween(1, 9999),
        'title' => fake()->sentence(),
        'html_url' => fake()->url(),
        'user' => null,
        'draft' => fake()->boolean(),
        'created_at' => fake()->iso8601(),
        'assignees' => [],
        'pull_request' => [
            'url' => fake()->url(),
        ],
    ];
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$pullRequestResponse])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests($collectionRunId, null);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertDatabaseHas(PullRequest::class, [
        'github_id' => $pullRequestResponse['node_id'],
        'collection_run_id' => $collectionRunId,
    ]);
});

test('handle chains the next page when there is one', function (): void {
    $nextPageUrl = fake()->url();
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([], 200, [
            'Link' => sprintf('<%s>; rel="next"', $nextPageUrl),
        ]),
    ]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests($collectionRunId, null);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $collectIssuesAndPullRequests->assertHasChain([new CollectIssuesAndPullRequests($collectionRunId, $nextPageUrl)]);
});

test('handle deletes the issues of another run on the last page', function (): void {
    $issue = Issue::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(CollectionRunId::generate(), null);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertModelMissing($issue);
});

test('handle deletes the pull requests of another run on the last page', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(CollectionRunId::generate(), null);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertModelMissing($pullRequest);
});

test('handle keeps the issues of another run before the last page', function (): void {
    $issue = Issue::factory()->create();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([], 200, [
            'Link' => sprintf('<%s>; rel="next"', fake()->url()),
        ]),
    ]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(CollectionRunId::generate(), null);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertModelExists($issue);
});
