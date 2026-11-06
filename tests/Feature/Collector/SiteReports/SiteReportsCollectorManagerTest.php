<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Models\SiteReport;

test('collector of a compass client source asks a site for its report with its key at its path', function (): void {
    $source = fake()->slug();
    $key = fake()->sha1();
    $path = sprintf('/%s', fake()->slug());
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::COMPASS_CLIENT,
            'key' => $key,
            'path' => $path,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->collector($source)->siteReport(SiteReport::factory()->create([
        'key' => null,
        'path' => null,
    ]));

    $http->assertSent(static function (HttpRequest $request) use ($key, $path): bool {
        return str_ends_with($request->url(), $path)
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $key));
    });
});

test('collector is the collector its extension makes from its config', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
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
        static function (Container $container, SourceConfig $sourceConfig) use ($compassClientSiteReportsCollector): CompassClientSiteReportsCollector {
            return $compassClientSiteReportsCollector;
        },
    );

    expect($siteReportsCollectorManager->collector($source))
        ->toBe($compassClientSiteReportsCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);

    $siteReportsCollectorManager->collector(fake()->slug());
})->throws(InvalidArgumentException::class);

test('collector refuses a source naming a driver that has no site reports collector', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => fake()->randomElement([BuiltInDriver::FORGE, fake()->slug()]),
        ],
    ]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);

    $siteReportsCollectorManager->collector($source);
})->throws(InvalidArgumentException::class);

test('collector refuses a compass client source with a setting it needs missing or unset', function (array $sourceConfig): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->collector($source);
})->with([
    'without a path' => [
        function (): array {
            return [
                'driver' => BuiltInDriver::COMPASS_CLIENT,
                'key' => fake()->sha1(),
            ];
        },
    ],
    'with an unset key' => [
        function (): array {
            return [
                'driver' => BuiltInDriver::COMPASS_CLIENT,
                'key' => null,
                'path' => sprintf('/%s', fake()->slug()),
            ];
        },
    ],
]);
