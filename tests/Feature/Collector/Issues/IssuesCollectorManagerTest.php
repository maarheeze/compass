<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubIssuesCollector;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Collector\Issues\IssueTypeMapping;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a github source collects the issues of its organisation with its token', function (): void {
    $key = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(['incomplete_results' => false, 'items' => []])]);

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);
    $issuesCollectorManager->collector($key)
        ->issues()
        ->current();

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), 'https://api.github.com/search/issues?')
            && str_starts_with($request['q'], sprintf('org:%s ', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('collector of a github source maps the issue types of its config', function (): void {
    $key = fake()->slug();
    $issueTypeName = fake()->word();
    $issueType = fake()->randomElement(IssueType::cases());
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'issue_types' => [$issueTypeName => $issueType],
        ],
    ]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'incomplete_results' => false,
            'items' => [
                [
                    'node_id' => fake()->sha1(),
                    'repository_url' => fake()->url(),
                    'number' => fake()->numberBetween(1, 9999),
                    'title' => fake()->sentence(),
                    'type' => ['name' => $issueTypeName],
                    'html_url' => fake()->url(),
                    'user' => null,
                    'created_at' => fake()->iso8601(),
                    'assignees' => [],
                ],
            ],
        ]),
    ]);

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);
    $collectedIssues = $issuesCollectorManager->collector($key)
        ->issues()
        ->current();

    expect($collectedIssues->sole()->type)
        ->toBe($issueType);
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
        new GitHubClient(
            $this->app->make(HttpFactory::class),
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        new IssueTypeMapping(new SourceConfig(fake()->slug(), [])),
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

    $issuesCollectorManager = $this->app->make(IssuesCollectorManager::class);
    $issuesCollectorManager->collector($key);
})->throws(InvalidArgumentException::class)->with([
    'without an organisation' => [
        function (): array {
            return [
                'driver' => 'github',
                'token' => fake()->sha1(),
                'requests_per_minute' => fake()->numberBetween(10, 100),
            ];
        },
    ],
    'with an unset token' => [
        function (): array {
            return [
                'driver' => 'github',
                'organisation' => fake()->slug(),
                'token' => null,
                'requests_per_minute' => fake()->numberBetween(10, 100),
            ];
        },
    ],
    'without requests per minute' => [
        function (): array {
            return [
                'driver' => 'github',
                'organisation' => fake()->slug(),
                'token' => fake()->sha1(),
            ];
        },
    ],
]);
