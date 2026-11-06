<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\PullRequests\Jobs\CollectPullRequests;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Uuid\Uuid;

test('handle stores the pull requests of the page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $pullRequestResponse = [
        'node_id' => fake()->sha1(),
        'repository_url' => sprintf(
            'https://api.github.com/repos/%s/%s',
            fake()->slug(),
            fake()->slug(),
        ),
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
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response(['incomplete_results' => false, 'items' => [$pullRequestResponse]]),
    ]);

    $collectPullRequests = new CollectPullRequests($runId, $source);
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertDatabaseHas(PullRequest::class, [
        'source' => $source,
        'external_id' => $pullRequestResponse['node_id'],
        'run_id' => $runId,
    ]);
});

test('handle deletes the pull requests of another run on the last page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $pullRequest = PullRequest::factory()->create(['source' => $source]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response(['incomplete_results' => false, 'items' => []]),
    ]);

    $collectPullRequests = new CollectPullRequests(Uuid::generate(), $source);
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertModelMissing($pullRequest);
});

test('handle keeps the pull requests collected on an earlier page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $pullRequestResponse = [
        'node_id' => fake()->sha1(),
        'repository_url' => sprintf(
            'https://api.github.com/repos/%s/%s',
            fake()->slug(),
            fake()->slug(),
        ),
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
    $pageResponses = [$pullRequestResponse];

    for ($itemIndex = 1; $itemIndex < 100; $itemIndex++) {
        $pageResponses[] = [...$pullRequestResponse, 'node_id' => fake()->unique()->sha1()];
    }

    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push(['incomplete_results' => false, 'items' => $pageResponses]);
    $responseSequence->push(['incomplete_results' => false, 'items' => []]);
    $http->fake(['*' => $responseSequence]);

    $collectPullRequests = new CollectPullRequests(Uuid::generate(), $source);
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertDatabaseHas(PullRequest::class, ['external_id' => $pullRequestResponse['node_id']]);
});

test('handle leaves the issues alone', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $issue = Issue::factory()->create(['source' => $source]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response(['incomplete_results' => false, 'items' => []]),
    ]);

    $collectPullRequests = new CollectPullRequests(Uuid::generate(), $source);
    $this->app->call([$collectPullRequests, 'handle']);

    $this->assertModelExists($issue);
});

test('handle refuses a source without a pull requests collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);

    $this->expectException(InvalidArgumentException::class);

    $collectPullRequests = new CollectPullRequests(Uuid::generate(), $key);
    $this->app->call([$collectPullRequests, 'handle']);
});
