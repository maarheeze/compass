<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpointCheck;

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
