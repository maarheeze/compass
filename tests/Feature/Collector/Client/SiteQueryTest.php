<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Client\ClientIncompatible;
use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Collector\Client\ClientNotInstalled;
use Maarheeze\Compass\Collector\Client\ClientUnauthorized;
use Maarheeze\Compass\Collector\Client\ClientUnreachable;
use Maarheeze\Compass\Collector\Client\ClientUrlFactory;
use Maarheeze\Compass\Collector\Client\SiteQuery;
use Maarheeze\Compass\Models\SiteReport;

test('report is sent to the client url of the site', function (): void {
    $siteReport = SiteReport::factory()->create();
    $clientUrlFactory = new ClientUrlFactory(sprintf('/%s', fake()->slug()));
    $clientUrl = $clientUrlFactory->create($siteReport);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), $clientUrlFactory, $http);
    $siteQuery->report($siteReport);

    $http->assertSent(static function (HttpRequest $request) use ($clientUrl): bool {
        return $request->url() === $clientUrl;
    });
});

test('report is sent with the key of the site', function (): void {
    $siteReport = SiteReport::factory()->create(['key' => fake()->sha1()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report($siteReport);

    $http->assertSent(static function (HttpRequest $request) use ($siteReport): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $siteReport->key));
    });
});

test('report holds what the client reported', function (): void {
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

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $clientReport = $siteQuery->report(SiteReport::factory()->create());

    expect($clientReport)
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
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $this->expectException(ClientNotInstalled::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the site answers without a site report', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(fake()->randomHtml())]);

    $this->expectException(ClientNotInstalled::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the client reports without a field', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
    ]),
    ]);

    $this->expectException(ClientIncompatible::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the client reports a field of another type', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->word(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $this->expectException(ClientIncompatible::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the client refuses the key', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 401)]);

    $this->expectException(ClientUnauthorized::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the client forbids the request', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 403)]);

    $this->expectException(ClientUnauthorized::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the site fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $this->expectException(ClientUnreachable::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});

test('report throws when the site cannot be reached', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::failedConnection()]);

    $this->expectException(ClientUnreachable::class);

    $siteQuery = new SiteQuery(new ClientKey(fake()->sha1()), new ClientUrlFactory(sprintf('/%s', fake()->slug())), $http);
    $siteQuery->report(SiteReport::factory()->create());
});
