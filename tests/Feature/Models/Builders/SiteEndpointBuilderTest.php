<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

test('of collected sites keeps an endpoint of a collected site', function (): void {
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->create(['missing_at' => null]))
        ->create();
    $siteEndpoint = SiteEndpoint::factory()
        ->for($siteReport)
        ->create();

    $siteEndpointsOfCollectedSites = SiteEndpoint::query()
        ->ofCollectedSites()
        ->get();

    expect($siteEndpointsOfCollectedSites->sole()->is($siteEndpoint))
        ->toBeTrue();
});

test('of collected sites leaves out an endpoint of a missing site', function (): void {
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->create(['missing_at' => fake()->dateTime()]))
        ->create();
    SiteEndpoint::factory()
        ->for($siteReport)
        ->create();

    $siteEndpointsOfCollectedSites = SiteEndpoint::query()
        ->ofCollectedSites()
        ->get();

    expect($siteEndpointsOfCollectedSites)
        ->toBeEmpty();
});
