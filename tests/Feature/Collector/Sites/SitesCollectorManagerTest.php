<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSitesCollector;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector of a forge source collects the sites of its organization with its token', function (): void {
    $key = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'forge',
            'organization' => $organization,
            'token' => $token,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesCollectorManager = $this->app->make(SitesCollectorManager::class);
    $sitesCollectorManager->collector($key)->sites()->current();

    $http->assertSent(static function (HttpRequest $request) use ($organization, $token): bool {
        return str_starts_with($request->url(), sprintf('https://forge.laravel.com/api/orgs/%s/sites?', $organization))
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
    $forgeSitesCollector = new ForgeSitesCollector(
        new ForgeClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );

    $sitesCollectorManager = $this->app->make(SitesCollectorManager::class);
    $sitesCollectorManager->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig) use ($forgeSitesCollector): ForgeSitesCollector {
            return $forgeSitesCollector;
        },
    );

    expect($sitesCollectorManager->collector($key))
        ->toBe($forgeSitesCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $sitesCollectorManager = $this->app->make(SitesCollectorManager::class);

    $sitesCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no sites collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->randomElement(['github', fake()->slug()]),
        ],
    ]);

    $sitesCollectorManager = $this->app->make(SitesCollectorManager::class);

    $sitesCollectorManager->collector($key);
})->throws(InvalidArgumentException::class);

test('collector refuses a forge source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [$key => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $sitesCollectorManager = $this->app->make(SitesCollectorManager::class);
    $sitesCollectorManager->collector($key);
})->with([
    'without an organization' => [
        function (): array {
            return [
                'driver' => 'forge',
                'token' => fake()->sha1(),
            ];
        },
    ],
    'with an unset token' => [
        function (): array {
            return [
                'driver' => 'forge',
                'organization' => fake()->slug(),
                'token' => null,
            ];
        },
    ],
]);
