<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubRepositoryFilesCollector;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a github source reads the files of its organisation with its token', function (): void {
    $key = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organisation' => $organisation,
            'token' => $token,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'content' => base64_encode(fake()->text()),
            'encoding' => 'base64',
        ]),
    ]);

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->collector($key)->content(fake()->slug(), fake()->sha1());

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), sprintf('https://api.github.com/repos/%s/', $organisation))
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
    $gitHubRepositoryFilesCollector = new GitHubRepositoryFilesCollector(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );

    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($gitHubRepositoryFilesCollector): GitHubRepositoryFilesCollector {
            return $gitHubRepositoryFilesCollector;
        },
    );

    expect($composerFilesCollectorManager->collector($key))
        ->toBe($gitHubRepositoryFilesCollector);
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
            'driver' => fake()->randomElement(['forge', fake()->slug()]),
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
    'without an organisation' => [
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
                'organisation' => fake()->slug(),
                'token' => null,
            ];
        },
    ],
]);
