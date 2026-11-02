<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

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
    $runId = Uuid::generate();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$pullRequestResponse])]);

    $collectPullRequests = new CollectPullRequests($runId);
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertDatabaseHas(PullRequest::class, [
        'github_id' => $pullRequestResponse['node_id'],
        'run_id' => $runId,
    ]);
});

test('handle deletes the pull requests of another run on the last page', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectPullRequests = new CollectPullRequests(Uuid::generate());
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertModelMissing($pullRequest);
});

test('handle keeps the pull requests collected on an earlier page', function (): void {
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
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([$pullRequestResponse], 200, [
        'Link' => sprintf('<%s>; rel="next"', fake()->url()),
    ]);
    $responseSequence->push([]);
    $http->fake(['*' => $responseSequence]);

    $collectPullRequests = new CollectPullRequests(Uuid::generate());
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertDatabaseHas(PullRequest::class, ['github_id' => $pullRequestResponse['node_id']]);
});

test('handle leaves the issues alone', function (): void {
    $issue = Issue::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectPullRequests = new CollectPullRequests(Uuid::generate());
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertModelExists($issue);
});
