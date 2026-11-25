<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSiteDomainsCollector;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainsCollectorManager;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Models\Site;

test('collector of a forge source collects the domains of a site of its organisation with its token', function (): void {
    $key = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'forge',
            'organisation' => $organisation,
            'token' => $token,
            'requests_per_minute' => fake()->numberBetween(10, 100),
        ],
    ]);
    $site = Site::factory()->create();
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteDomainsCollectorManager = $this->app->make(SiteDomainsCollectorManager::class);
    $siteDomainsCollectorManager->collector($key)->siteDomains($site);

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), sprintf('https://forge.laravel.com/api/orgs/%s/', $organisation))
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
    $forgeSiteDomainsCollector = new ForgeSiteDomainsCollector(
        new ForgeClient(
            $this->app->make(HttpFactory::class),
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );

    $siteDomainsCollectorManager = $this->app->make(SiteDomainsCollectorManager::class);
    $siteDomainsCollectorManager->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig) use ($forgeSiteDomainsCollector): ForgeSiteDomainsCollector {
            return $forgeSiteDomainsCollector;
        },
    );

    expect($siteDomainsCollectorManager->collector($key))
        ->toBe($forgeSiteDomainsCollector);
});
