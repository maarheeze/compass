<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubIssuesCollector;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a github source collects the issues of its organization with its token', function (): void {
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

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);
    $issuesCollectorManager->collector($key)->issues()->current();

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
    $gitHubIssuesCollector = new GitHubIssuesCollector(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);
    $issuesCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($gitHubIssuesCollector): GitHubIssuesCollector {
            return $gitHubIssuesCollector;
        },
    );

    expect($issuesCollectorManager->collector($key))
        ->toBe($gitHubIssuesCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);

    $issuesCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no issues collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->randomElement(['forge', fake()->slug()]),
        ],
    ]);

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);

    $issuesCollectorManager->collector($key);
})->throws(InvalidArgumentException::class);

test('collector refuses a github source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [$key => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);
    $issuesCollectorManager->collector($key);
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
