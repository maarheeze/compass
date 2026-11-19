<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('down keeps a down check', function (): void {
    $downSiteEndpointCheck = SiteEndpointCheck::factory()->create(['state' => SiteEndpointState::DOWN]);

    $downSiteEndpointChecks = SiteEndpointCheck::query()
        ->down()
        ->get();

    expect($downSiteEndpointChecks->sole()->is($downSiteEndpointCheck))
        ->toBeTrue();
});

test('down leaves out an up check', function (): void {
    SiteEndpointCheck::factory()->create(['state' => SiteEndpointState::UP]);

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
        'path' => $siteEndpointOfAnotherSite->path,
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
        'site_id' => $siteEndpoint->siteReport->site_id,
        'path' => $siteEndpoint->path,
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

test('with online since is the first successful check after the latest failed one', function (): void {
    $site = Site::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->slug()));
    SiteEndpointCheck::factory()->create([
        'site_id' => $site->id,
        'path' => $endpointPath,
        'state' => SiteEndpointState::UP,
        'created_at' => fake()->dateTimeBetween('-4 months', '-3 months'),
    ]);
    SiteEndpointCheck::factory()->create([
        'site_id' => $site->id,
        'path' => $endpointPath,
        'state' => SiteEndpointState::DOWN,
        'created_at' => fake()->dateTimeBetween('-3 months', '-2 months'),
    ]);
    $recoveredSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $site->id,
        'path' => $endpointPath,
        'state' => SiteEndpointState::UP,
        'created_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $latestSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $site->id,
        'path' => $endpointPath,
        'state' => SiteEndpointState::UP,
        'created_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $siteEndpointCheckWithOnlineSince = SiteEndpointCheck::query()
        ->withOnlineSince()
        ->whereKey($latestSiteEndpointCheck->id)
        ->sole();

    expect($siteEndpointCheckWithOnlineSince->online_since?->toDateTimeString())
        ->toBe($recoveredSiteEndpointCheck->created_at->toDateTimeString());
});

test('with online since is the first check of a path that never failed', function (): void {
    $firstSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'state' => SiteEndpointState::UP,
        'created_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $latestSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $firstSiteEndpointCheck->site_id,
        'path' => $firstSiteEndpointCheck->path,
        'state' => SiteEndpointState::UP,
        'created_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $siteEndpointCheckWithOnlineSince = SiteEndpointCheck::query()
        ->withOnlineSince()
        ->whereKey($latestSiteEndpointCheck->id)
        ->sole();

    expect($siteEndpointCheckWithOnlineSince->online_since?->toDateTimeString())
        ->toBe($firstSiteEndpointCheck->created_at->toDateTimeString());
});

test('with online since is empty while the path is down', function (): void {
    $successfulSiteEndpointCheck = SiteEndpointCheck::factory()->create(['state' => SiteEndpointState::UP]);
    $failedSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $successfulSiteEndpointCheck->site_id,
        'path' => $successfulSiteEndpointCheck->path,
        'state' => SiteEndpointState::DOWN,
    ]);

    $siteEndpointCheckWithOnlineSince = SiteEndpointCheck::query()
        ->withOnlineSince()
        ->whereKey($failedSiteEndpointCheck->id)
        ->sole();

    expect($siteEndpointCheckWithOnlineSince->online_since)
        ->toBeNull();
});
