<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssuesAndPullRequests;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\Repository;

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
    $runId = RunId::generate();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$issueResponse])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests($runId);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertDatabaseHas(Issue::class, [
        'github_id' => $issueResponse['node_id'],
        'run_id' => $runId,
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
    $runId = RunId::generate();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$pullRequestResponse])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests($runId);
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertDatabaseHas(PullRequest::class, [
        'github_id' => $pullRequestResponse['node_id'],
        'run_id' => $runId,
    ]);
});

test('handle deletes the issues of another run on the last page', function (): void {
    $issue = Issue::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(RunId::generate());
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertModelMissing($issue);
});

test('handle deletes the pull requests of another run on the last page', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(RunId::generate());
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertModelMissing($pullRequest);
});

test('handle keeps the issues collected on an earlier page', function (): void {
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
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([$issueResponse], 200, [
        'Link' => sprintf('<%s>; rel="next"', fake()->url()),
    ]);
    $responseSequence->push([]);
    $http->fake(['*' => $responseSequence]);

    $collectIssuesAndPullRequests = new CollectIssuesAndPullRequests(RunId::generate());
    $this->app->call([$collectIssuesAndPullRequests, 'handle']);

    $this->assertDatabaseHas(Issue::class, ['github_id' => $issueResponse['node_id']]);
});
