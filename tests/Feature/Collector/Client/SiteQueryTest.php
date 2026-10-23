<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Client\ClientNotInstalled;
use Maarheeze\Compass\Collector\Client\ClientUnauthorized;
use Maarheeze\Compass\Collector\Client\ClientUnreachable;
use Maarheeze\Compass\Collector\Client\ClientUrlFactory;
use Maarheeze\Compass\Collector\Client\SiteQuery;
use Maarheeze\Compass\Models\Site;

test('report is sent to the client url of the site', function (): void {
    $site = Site::factory()->create();
    $clientUrl = $this->app->make(ClientUrlFactory::class)->create($site);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report($site);

    $http->assertSent(static function (HttpRequest $request) use ($clientUrl): bool {
        return $request->url() === $clientUrl;
    });
});

test('report is sent with the key of the site', function (): void {
    $site = Site::factory()->create(['client_key' => fake()->sha1()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report($site);

    $http->assertSent(static function (HttpRequest $request) use ($site): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $site->client_key));
    });
});

test('report holds what the client reported', function (): void {
    $appDebug = fake()->boolean();
    $appEnv = fake()->word();
    $appName = fake()->company();
    $endpoints = [sprintf('/%s', fake()->unique()->slug()), sprintf('/%s', fake()->unique()->slug())];
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([
        'app_debug' => $appDebug,
        'app_env' => $appEnv,
        'app_name' => $appName,
        'endpoints' => $endpoints,
    ]),
    ]);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteReport = $siteQuery->report(Site::factory()->create());

    expect($siteReport)
        ->appDebug
        ->toBe($appDebug)
        ->appEnv
        ->toBe($appEnv)
        ->appName
        ->toBe($appName)
        ->endpoints
        ->toBe($endpoints);
});

test('report throws when the site has no client', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $this->expectException(ClientNotInstalled::class);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report(Site::factory()->create());
});

test('report throws when the site answers without a site report', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(fake()->randomHtml())]);

    $this->expectException(ClientNotInstalled::class);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report(Site::factory()->create());
});

test('report throws when the client refuses the key', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $this->expectException(ClientUnauthorized::class);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report(Site::factory()->create());
});

test('report throws when the client forbids the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 403)]);

    $this->expectException(ClientUnauthorized::class);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report(Site::factory()->create());
});

test('report throws when the site fails', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 500)]);

    $this->expectException(ClientUnreachable::class);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report(Site::factory()->create());
});

test('report throws when the site cannot be reached', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $this->expectException(ClientUnreachable::class);

    $siteQuery = $this->app->make(SiteQuery::class);
    $siteQuery->report(Site::factory()->create());
});
