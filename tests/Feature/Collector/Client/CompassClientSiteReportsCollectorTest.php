<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Client\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Models\Site;

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

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(Site::factory()->create());

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

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(Site::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::incompatible());
});

test('site report is not installed when the site has no client', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(Site::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::notInstalled());
});

test('site report is unauthorized when the client refuses the key', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 401)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $collectedSiteReport = $compassClientSiteReportsCollector->siteReport(Site::factory()->create());

    expect($collectedSiteReport)
        ->toEqual(CollectedSiteReport::unauthorized());
});

test('site report throws collecting failed when the site cannot be reached', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::failedConnection()]);

    $this->expectException(CollectingFailed::class);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $compassClientSiteReportsCollector->siteReport(Site::factory()->create());
});

test('site report is sent with the key of the source for a site without a key of its own', function (): void {
    $key = fake()->sha1();
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, $key, sprintf('/%s', fake()->slug()));
    $compassClientSiteReportsCollector->siteReport(Site::factory()->create(['client_key' => null]));

    $http->assertSent(static function (HttpRequest $request) use ($key): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $key));
    });
});

test('site report is sent to the path of the source for a site without a path of its own', function (): void {
    $path = sprintf('/%s', fake()->slug());
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $compassClientSiteReportsCollector = new CompassClientSiteReportsCollector($http, fake()->sha1(), $path);
    $compassClientSiteReportsCollector->siteReport(Site::factory()->create(['client_path' => null]));

    $http->assertSent(static function (HttpRequest $request) use ($path): bool {
        return str_ends_with($request->url(), $path);
    });
});
