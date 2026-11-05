<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubPullRequestsCollector;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollectorManager;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a github source collects the pull requests of its organization with its token', function (): void {
    $key = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organization' => $organization,
            'token' => $token,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $pullRequestsCollectorManager = $this->app->make(PullRequestsCollectorManager::class);
    $pullRequestsCollectorManager->collector($key)->pullRequests()->current();

    $http->assertSent(static function (HttpRequest $request) use ($organization, $token): bool {
        return str_starts_with($request->url(), sprintf('https://api.github.com/orgs/%s/issues?', $organization))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('collector is the collector its extension makes from its config', function (): void {
    $key = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => $driverName,
        ],
    ]);
    $gitHubPullRequestsCollector = new GitHubPullRequestsCollector(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );

    $pullRequestsCollectorManager = $this->app->make(PullRequestsCollectorManager::class);
    $pullRequestsCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($gitHubPullRequestsCollector): GitHubPullRequestsCollector {
            return $gitHubPullRequestsCollector;
        },
    );

    expect($pullRequestsCollectorManager->collector($key))
        ->toBe($gitHubPullRequestsCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $pullRequestsCollectorManager = $this->app->make(PullRequestsCollectorManager::class);

    $pullRequestsCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no pull requests collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->randomElement(['forge', fake()->slug()]),
        ],
    ]);

    $pullRequestsCollectorManager = $this->app->make(PullRequestsCollectorManager::class);

    $pullRequestsCollectorManager->collector($key);
})->throws(InvalidArgumentException::class);

test('collector refuses a github source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [$key => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $pullRequestsCollectorManager = $this->app->make(PullRequestsCollectorManager::class);
    $pullRequestsCollectorManager->collector($key);
})->with([
    'without an organization' => [
        function (): array {
            return [
                'driver' => 'github',
                'token' => fake()->sha1(),
            ];
        },
    ],
    'with an unset token' => [
        function (): array {
            return [
                'driver' => 'github',
                'organization' => fake()->slug(),
                'token' => null,
            ];
        },
    ],
]);
