<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Client\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Models\SiteReport;

test('collector of a compass client source asks a site for its report with its key at its path', function (): void {
    $sourceKey = fake()->slug();
    $key = fake()->sha1();
    $path = sprintf('/%s', fake()->slug());
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'compass-client',
            'key' => $key,
            'path' => $path,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->collector($sourceKey)->siteReport(SiteReport::factory()->create([
        'key' => null,
        'path' => null,
    ]));

    $http->assertSent(static function (HttpRequest $request) use ($key, $path): bool {
        return str_ends_with($request->url(), $path)
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $key));
    });
});

test('collector is the collector its extension makes from its config', function (): void {
    $sourceKey = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => $driverName,
        ],
    ]);
    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        sprintf('/%s', fake()->slug()),
    );

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->extend(
        $driverName,
        static function (Container $container, array $sourceConfig) use ($compassClientSiteReportsCollector): CompassClientSiteReportsCollector {
            return $compassClientSiteReportsCollector;
        },
    );

    expect($siteReportsCollectorManager->collector($sourceKey))
        ->toBe($compassClientSiteReportsCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);

    $siteReportsCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no site reports collector', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => fake()->randomElement(['forge', fake()->slug()]),
        ],
    ]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);

    $siteReportsCollectorManager->collector($sourceKey);
})->throws(InvalidArgumentException::class);

test('collector refuses a compass client source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [$sourceKey => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->collector($sourceKey);
})->with([
    'without a path' => [
        function (): array {
            return [
                'driver' => 'compass-client',
                'key' => fake()->sha1(),
            ];
        },
    ],
    'with an unset key' => [
        function (): array {
            return [
                'driver' => 'compass-client',
                'key' => null,
                'path' => sprintf('/%s', fake()->slug()),
            ];
        },
    ],
]);
