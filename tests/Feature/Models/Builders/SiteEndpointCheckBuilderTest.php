<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('down keeps an unsuccessful check', function (): void {
    $unsuccessfulSiteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => false]);

    $downSiteEndpointChecks = SiteEndpointCheck::query()
        ->down()
        ->get();

    expect($downSiteEndpointChecks->sole()->is($unsuccessfulSiteEndpointCheck))
        ->toBeTrue();
});

test('down leaves out a successful check', function (): void {
    SiteEndpointCheck::factory()->create(['is_successful' => true]);

    $downSiteEndpointChecks = SiteEndpointCheck::query()
        ->down()
        ->get();

    expect($downSiteEndpointChecks)
        ->toBeEmpty();
});

test('latest per path leaves out an earlier check of the same path', function (): void {
    $earlierSiteEndpointCheck = SiteEndpointCheck::factory()->create();
    $latestSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $earlierSiteEndpointCheck->site_id,
        'path' => $earlierSiteEndpointCheck->path,
    ]);

    $latestSiteEndpointCheckIds = SiteEndpointCheck::query()
        ->latestPerPath()
        ->pluck('id')
        ->all();

    expect($latestSiteEndpointCheckIds)
        ->toEqual([$latestSiteEndpointCheck->id]);
});

test('latest per path keeps the latest check of each path of a site', function (): void {
    $site = Site::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $site->id,
        'path' => new EndpointPath(sprintf('/%s', fake()->unique()->slug())),
    ]);
    $siteEndpointCheckOfAnotherPath = SiteEndpointCheck::factory()->create([
        'site_id' => $site->id,
        'path' => new EndpointPath(sprintf('/%s', fake()->unique()->slug())),
    ]);

    $latestSiteEndpointCheckIds = SiteEndpointCheck::query()
        ->latestPerPath()
        ->pluck('id')
        ->all();

    expect($latestSiteEndpointCheckIds)
        ->toEqualCanonicalizing([$siteEndpointCheck->id, $siteEndpointCheckOfAnotherPath->id]);
});

test('latest per path keeps the latest check of the same path on each site', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $siteEndpointCheckOfAnotherSite = SiteEndpointCheck::factory()->create(['path' => $siteEndpointCheck->path]);

    $latestSiteEndpointCheckIds = SiteEndpointCheck::query()
        ->latestPerPath()
        ->pluck('id')
        ->all();

    expect($latestSiteEndpointCheckIds)
        ->toEqualCanonicalizing([$siteEndpointCheck->id, $siteEndpointCheckOfAnotherSite->id]);
});

test('not reported keeps a check of a path only another site reports', function (): void {
    $siteEndpointOfAnotherSite = SiteEndpoint::factory()->create();
    $notReportedSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'path' => new EndpointPath($siteEndpointOfAnotherSite->path),
    ]);

    $notReportedSiteEndpointChecks = SiteEndpointCheck::query()
        ->notReported()
        ->get();

    expect($notReportedSiteEndpointChecks->sole()->is($notReportedSiteEndpointCheck))
        ->toBeTrue();
});

test('not reported leaves out a check of a path its site reports', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
    ]);

    $notReportedSiteEndpointChecks = SiteEndpointCheck::query()
        ->notReported()
        ->get();

    expect($notReportedSiteEndpointChecks)
        ->toBeEmpty();
});

test('of path keeps a check of the path', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();

    $siteEndpointChecksOfPath = SiteEndpointCheck::query()
        ->ofPath($siteEndpointCheck->path)
        ->get();

    expect($siteEndpointChecksOfPath->sole()->is($siteEndpointCheck))
        ->toBeTrue();
});

test('of path leaves out a check of another path', function (): void {
    SiteEndpointCheck::factory()->create();

    $siteEndpointChecksOfPath = SiteEndpointCheck::query()
        ->ofPath(new EndpointPath(sprintf('/%s', fake()->unique()->slug())))
        ->get();

    expect($siteEndpointChecksOfPath)
        ->toBeEmpty();
});

test('reported keeps a check of a path its site reports', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $reportedSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
    ]);

    $reportedSiteEndpointChecks = SiteEndpointCheck::query()
        ->reported()
        ->get();

    expect($reportedSiteEndpointChecks->sole()->is($reportedSiteEndpointCheck))
        ->toBeTrue();
});

test('reported leaves out a check of a path only another site reports', function (): void {
    $siteEndpointOfAnotherSite = SiteEndpoint::factory()->create();
    SiteEndpointCheck::factory()->create(['path' => new EndpointPath($siteEndpointOfAnotherSite->path)]);

    $reportedSiteEndpointChecks = SiteEndpointCheck::query()
        ->reported()
        ->get();

    expect($reportedSiteEndpointChecks)
        ->toBeEmpty();
});
