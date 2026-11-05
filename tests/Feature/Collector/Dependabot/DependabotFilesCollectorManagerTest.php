<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubDependabotFilesCollector;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a github source reads the files of its organization with its token', function (): void {
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
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => fake()->text(),
                    ],
                ],
            ],
        ]),
    ]);

    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);
    $dependabotFilesCollectorManager->collector($key)->content(fake()->slug(), fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($organization, $token): bool {
        return $request['variables']['organization'] === $organization
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
    $gitHubDependabotFilesCollector = new GitHubDependabotFilesCollector(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );

    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);
    $dependabotFilesCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($gitHubDependabotFilesCollector): GitHubDependabotFilesCollector {
            return $gitHubDependabotFilesCollector;
        },
    );

    expect($dependabotFilesCollectorManager->collector($key))
        ->toBe($gitHubDependabotFilesCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);

    $dependabotFilesCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no dependabot files collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->randomElement(['forge', fake()->slug()]),
        ],
    ]);

    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);

    $dependabotFilesCollectorManager->collector($key);
})->throws(InvalidArgumentException::class);

test('collector refuses a github source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [$key => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);
    $dependabotFilesCollectorManager->collector($key);
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
