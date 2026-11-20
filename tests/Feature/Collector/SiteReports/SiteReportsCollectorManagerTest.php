<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Console\ScheduledCommands;
use Maarheeze\Compass\Models\SiteReport;
use Random\Randomizer;

test('collector of a compass client source asks a site for its report at its path', function (): void {
    $source = fake()->slug();
    $path = sprintf('/%s', fake()->slug());
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => $path,
        ],
    ]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->collector($source)->siteReport(SiteReport::factory()->create(['path' => null]));

    $http->assertSent(static function (HttpRequest $request) use ($path): bool {
        return str_ends_with($request->url(), $path);
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
        new Randomizer(),
        new SiteClient(
            $this->app->make(HttpFactory::class),
            sprintf('/%s', fake()->slug()),
            $this->app->make(ScheduledCommands::class),
            fake()->slug(),
            $this->app->make(UrlGenerator::class),
        ),
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
            'driver' => fake()->randomElement(['forge', fake()->slug()]),
        ],
    ]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);

    $siteReportsCollectorManager->collector($source);
})->throws(InvalidArgumentException::class);

test('collector refuses a compass client source without a path', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
        ],
    ]);

    $siteReportsCollectorManager = $this->app->make(SiteReportsCollectorManager::class);
    $siteReportsCollectorManager->collector($source);
})->throws(InvalidArgumentException::class);
