<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoint;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('handle stores the status code of the endpoint', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $statusCode = fake()->numberBetween(200, 599);
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, $statusCode)]);

    $checkSiteEndpoint = new CheckSiteEndpoint($siteEndpoint);
    $this->app->call([$checkSiteEndpoint, 'handle']);

    $this->assertDatabaseHas(SiteEndpointCheck::class, [
        'site_id' => $siteEndpoint->site_id,
        'path' => $siteEndpoint->path,
        'status_code' => $statusCode,
    ]);
});

test('handle stores the checked url', function (): void {
    $siteEndpoint = SiteEndpoint::factory()
        ->for(Site::factory()->state(['url' => 'https://example.com/']))
        ->create(['path' => '/up']);
    $this->app->make(HttpFactory::class)->fake();

    $checkSiteEndpoint = new CheckSiteEndpoint($siteEndpoint);
    $this->app->call([$checkSiteEndpoint, 'handle']);

    $this->assertDatabaseHas(SiteEndpointCheck::class, [
        'site_id' => $siteEndpoint->site_id,
        'url' => 'https://example.com/up',
    ]);
});

test('handle does not follow a redirect', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 301, ['Location' => fake()->url()])]);

    $checkSiteEndpoint = new CheckSiteEndpoint($siteEndpoint);
    $this->app->call([$checkSiteEndpoint, 'handle']);

    $this->assertDatabaseHas(SiteEndpointCheck::class, [
        'site_id' => $siteEndpoint->site_id,
        'status_code' => 301,
    ]);
});

test('handle stores no status code for an endpoint that cannot be reached', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $checkSiteEndpoint = new CheckSiteEndpoint($siteEndpoint);
    $this->app->call([$checkSiteEndpoint, 'handle']);

    $this->assertDatabaseHas(SiteEndpointCheck::class, [
        'site_id' => $siteEndpoint->site_id,
        'status_code' => null,
    ]);
});

test('handle adds the site and path of the endpoint to the log context', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $this->app->make(HttpFactory::class)->fake();

    $checkSiteEndpoint = new CheckSiteEndpoint($siteEndpoint);
    $this->app->call([$checkSiteEndpoint, 'handle']);

    expect($this->app->make(Context::class)->only(['site', 'path']))
        ->toBe([
            'site' => $siteEndpoint->site->name,
            'path' => $siteEndpoint->path,
        ]);
});
