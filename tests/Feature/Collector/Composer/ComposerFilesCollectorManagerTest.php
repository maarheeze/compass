<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubComposerFilesCollector;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a github source reads the files of its organization with its token', function (): void {
    $key = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => BuiltInDriver::GITHUB,
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

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->collector($key)->content(fake()->slug(), fake()->sha1());

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
    $gitHubComposerFilesCollector = new GitHubComposerFilesCollector(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($gitHubComposerFilesCollector): GitHubComposerFilesCollector {
            return $gitHubComposerFilesCollector;
        },
    );

    expect($composerFilesCollectorManager->collector($key))
        ->toBe($gitHubComposerFilesCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);

    $composerFilesCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no composer files collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->randomElement([BuiltInDriver::FORGE, fake()->slug()]),
        ],
    ]);

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);

    $composerFilesCollectorManager->collector($key);
})->throws(InvalidArgumentException::class);

test('collector refuses a github source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [$key => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->collector($key);
})->with([
    'without an organization' => [
        function (): array {
            return [
                'driver' => BuiltInDriver::GITHUB,
                'token' => fake()->sha1(),
            ];
        },
    ],
    'with an unset token' => [
        function (): array {
            return [
                'driver' => BuiltInDriver::GITHUB,
                'organization' => fake()->slug(),
                'token' => null,
            ];
        },
    ],
]);
