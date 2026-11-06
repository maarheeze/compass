<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\CompassClient\ClientUnreachable;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('get requests the path of the site report from its site', function (): void {
    $siteReport = SiteReport::factory()->create(['path' => sprintf('/%s', fake()->slug())]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $siteClient->get($siteReport);

    $http->assertSent(static function (HttpRequest $request) use ($siteReport): bool {
        return $request->url() === sprintf('%s%s', rtrim($siteReport->site->url, '/'), $siteReport->path);
    });
});

test('get requests the path of the source for a site report without one', function (): void {
    $siteReport = SiteReport::factory()->create(['path' => null]);
    $path = sprintf('/%s', fake()->slug());
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient($http, fake()->sha1(), $path);
    $siteClient->get($siteReport);

    $http->assertSent(static function (HttpRequest $request) use ($path, $siteReport): bool {
        return $request->url() === sprintf('%s%s', rtrim($siteReport->site->url, '/'), $path);
    });
});

test('get joins a site url ending in a slash with one slash', function (): void {
    $host = fake()->domainName();
    $clientPath = fake()->slug();
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['url' => sprintf('https://%s/', $host)]))
        ->create(['path' => sprintf('/%s', $clientPath)]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $siteClient->get($siteReport);

    $http->assertSent(static function (HttpRequest $request) use ($clientPath, $host): bool {
        return $request->url() === sprintf('https://%s/%s', $host, $clientPath);
    });
});

test('get sends the key of the site report', function (): void {
    $siteReport = SiteReport::factory()->create(['key' => fake()->unique()->sha1()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient($http, fake()->unique()->sha1(), sprintf('/%s', fake()->slug()));
    $siteClient->get($siteReport);

    $http->assertSent(static function (HttpRequest $request) use ($siteReport): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $siteReport->key));
    });
});

test('get sends the key of the source for a site report without one', function (): void {
    $key = fake()->sha1();
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $siteClient = new SiteClient($http, $key, sprintf('/%s', fake()->slug()));
    $siteClient->get(SiteReport::factory()->create(['key' => null]));

    $http->assertSent(static function (HttpRequest $request) use ($key): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $key));
    });
});

test('get throws when the site cannot be reached', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::failedConnection()]);

    $this->expectException(ClientUnreachable::class);

    $siteClient = new SiteClient($http, fake()->sha1(), sprintf('/%s', fake()->slug()));
    $siteClient->get(SiteReport::factory()->create());
});
