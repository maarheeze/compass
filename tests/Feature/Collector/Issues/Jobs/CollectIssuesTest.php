<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\Issues\Jobs\CollectIssues;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Uuid\Uuid;

test('handle stores the issues of the page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $issueResponse = [
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
        'created_at' => fake()->iso8601(),
        'assignees' => [],
    ];
    $runId = Uuid::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response(['incomplete_results' => false, 'items' => [$issueResponse]]),
    ]);

    $collectIssues = new CollectIssues($runId, $source);
    $this->app->call([$collectIssues, 'handle']);

    $this->assertDatabaseHas(Issue::class, [
        'source' => $source,
        'external_id' => $issueResponse['node_id'],
        'run_id' => $runId,
    ]);
});

test('handle deletes the issues of another run on the last page', function (): void {
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

    $collectIssues = new CollectIssues(Uuid::generate(), $source);
    $this->app->call([$collectIssues, 'handle']);

    $this->assertModelMissing($issue);
});

test('handle keeps the issues collected on an earlier page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $issueResponse = [
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
        'created_at' => fake()->iso8601(),
        'assignees' => [],
    ];
    $pageResponses = [$issueResponse];

    for ($itemIndex = 1; $itemIndex < 100; $itemIndex++) {
        $pageResponses[] = [...$issueResponse, 'node_id' => fake()->unique()->sha1()];
    }

    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push(['incomplete_results' => false, 'items' => $pageResponses]);
    $responseSequence->push(['incomplete_results' => false, 'items' => []]);
    $http->fake(['*' => $responseSequence]);

    $collectIssues = new CollectIssues(Uuid::generate(), $source);
    $this->app->call([$collectIssues, 'handle']);

    $this->assertDatabaseHas(Issue::class, ['external_id' => $issueResponse['node_id']]);
});

test('handle leaves the pull requests alone', function (): void {
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

    $collectIssues = new CollectIssues(Uuid::generate(), $source);
    $this->app->call([$collectIssues, 'handle']);

    $this->assertModelExists($pullRequest);
});

test('handle refuses a source without an issues collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);

    $this->expectException(InvalidArgumentException::class);

    $collectIssues = new CollectIssues(Uuid::generate(), $key);
    $this->app->call([$collectIssues, 'handle']);
});

test('lock key is the source', function (): void {
    $source = fake()->slug();

    $collectIssues = new CollectIssues(Uuid::generate(), $source);
    $lockKey = $collectIssues->lockKey();

    expect($lockKey)
        ->toBe($source);
});
