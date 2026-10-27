<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\SiteEndpoint\DownSiteEndpointAlertDetector;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('find alerts finds an endpoint whose latest check failed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
        'is_successful' => false,
    ]);

    $downSiteEndpointAlertDetector = $this->app->make(DownSiteEndpointAlertDetector::class);
    $alerts = $downSiteEndpointAlertDetector->findAlerts();

    expect($alerts->sole()->siteEndpointCheck->is($siteEndpointCheck))
        ->toBeTrue();
});

test('find alerts leaves out an endpoint whose latest check succeeded', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
        'is_successful' => false,
    ]);
    SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
        'is_successful' => true,
    ]);

    $downSiteEndpointAlertDetector = $this->app->make(DownSiteEndpointAlertDetector::class);
    $alerts = $downSiteEndpointAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out an endpoint that is no longer reported', function (): void {
    SiteEndpointCheck::factory()->create(['is_successful' => false]);

    $downSiteEndpointAlertDetector = $this->app->make(DownSiteEndpointAlertDetector::class);
    $alerts = $downSiteEndpointAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out the endpoints of a missing site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()
        ->for(Site::factory()->state(['missing_at' => fake()->dateTime()]))
        ->create();
    SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
        'is_successful' => false,
    ]);

    $downSiteEndpointAlertDetector = $this->app->make(DownSiteEndpointAlertDetector::class);
    $alerts = $downSiteEndpointAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
