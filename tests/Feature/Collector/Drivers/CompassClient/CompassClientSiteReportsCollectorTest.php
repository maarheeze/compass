<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Random\Randomizer;

test('site report holds what the client reported', function (): void {
    $appDebug = fake()->boolean();
    $appEnv = fake()->word();
    $appName = fake()->company();
    $endpoints = [sprintf('/%s', fake()->unique()->slug()), sprintf('/%s', fake()->unique()->slug())];
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => $appDebug,
        'app_env' => $appEnv,
        'app_name' => $appName,
        'endpoints' => $endpoints,
    ]),
    ]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::installed($appDebug, $appEnv, $appName, $endpoints));
});

test('site report is incompatible when the client reports without a field', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
    ]),
    ]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::incompatible());
});

test('site report is not installed when the site has no client', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::notInstalled());
});

test('site report is unauthorized when the client refuses the key', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 401)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::unauthorized());
});

test('site report throws collecting failed when the site cannot be reached', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::failedConnection()]);

    $this->expectException(CollectingFailed::class);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->create());
});

test('site report asks the site with its key', function (): void {
    $site = Site::factory()->create(['key' => fake()->sha1()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->for($site)->create());

    $http->assertSent(static function (HttpRequest $request) use ($site): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $site->key));
    });
});

test('site report gives a site without a key a new one', function (): void {
    $site = Site::factory()->create(['key' => null]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->for($site)->create());

    expect($site->refresh()->key)
        ->toMatch('/\A[0-9a-f]{64}\z/');
});

test('site report asks a site without a key with the key it gave it', function (): void {
    $site = Site::factory()->create(['key' => null]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector(
        new Randomizer(),
        new SiteClient($http, sprintf('/%s', fake()->slug())),
    );
    $compassClientSiteReportsCollector->siteReport(SiteReport::factory()->for($site)->create());

    $http->assertSent(static function (HttpRequest $request) use ($site): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $site->refresh()->key));
    });
});
