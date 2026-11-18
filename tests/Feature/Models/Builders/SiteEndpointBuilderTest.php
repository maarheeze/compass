<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
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

test('due for check keeps an endpoint of a collected site whose due time has passed', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->create(['missing_at' => null])))
        ->create(['due_at' => $now->subMinutes(fake()->numberBetween(0, 60))]);

    $siteEndpointsDueForCheck = SiteEndpoint::query()
        ->dueForCheck($now)
        ->get();

    expect($siteEndpointsDueForCheck->sole()->is($siteEndpoint))
        ->toBeTrue();
});

test('due for check leaves out an endpoint that is not due yet', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->create(['missing_at' => null])))
        ->create(['due_at' => $now->addMinutes(fake()->numberBetween(1, 60))]);

    $siteEndpointsDueForCheck = SiteEndpoint::query()
        ->dueForCheck($now)
        ->get();

    expect($siteEndpointsDueForCheck)
        ->toBeEmpty();
});

test('due for check leaves out an endpoint of a missing site', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->create(['missing_at' => fake()->dateTime()])))
        ->create(['due_at' => $now->subMinutes(fake()->numberBetween(0, 60))]);

    $siteEndpointsDueForCheck = SiteEndpoint::query()
        ->dueForCheck($now)
        ->get();

    expect($siteEndpointsDueForCheck)
        ->toBeEmpty();
});

test('of path keeps only the endpoint of the path', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpoint::factory()->create();

    $siteEndpointsOfPath = SiteEndpoint::query()
        ->ofPath($siteEndpoint->path)
        ->get();

    expect($siteEndpointsOfPath->sole()->is($siteEndpoint))
        ->toBeTrue();
});

test('of site keeps only the endpoints of the site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpoint::factory()->create();

    $siteEndpointsOfSite = SiteEndpoint::query()
        ->ofSite($siteEndpoint->siteReport->site)
        ->get();

    expect($siteEndpointsOfSite->sole()->is($siteEndpoint))
        ->toBeTrue();
});

test('with latest check loads the latest check of the path of the site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->siteReport->site_id,
        'path' => $siteEndpoint->path,
    ]);
    $latestSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->siteReport->site_id,
        'path' => $siteEndpoint->path,
    ]);

    $siteEndpointWithLatestCheck = SiteEndpoint::query()
        ->withLatestCheck()
        ->sole();

    expect($siteEndpointWithLatestCheck->latestCheck?->is($latestSiteEndpointCheck))
        ->toBeTrue();
});

test('with latest check leaves out the check of the path of another site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpointCheck::factory()->create(['path' => $siteEndpoint->path]);

    $siteEndpointWithLatestCheck = SiteEndpoint::query()
        ->withLatestCheck()
        ->sole();

    expect($siteEndpointWithLatestCheck->latestCheck)
        ->toBeNull();
});

test('with latest check loads when the check has been online since', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->siteReport->site_id,
        'path' => $siteEndpoint->path,
        'is_successful' => true,
    ]);

    $siteEndpointWithLatestCheck = SiteEndpoint::query()
        ->withLatestCheck()
        ->sole();

    expect($siteEndpointWithLatestCheck->latestCheck?->online_since)
        ->toBeInstanceOf(CarbonImmutable::class);
});
