<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\View\Components\SiteEndpointTable;

test('down site endpoints are the endpoints whose latest check failed', function (): void {
    $downSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN]));
    $upSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP]));

    $siteEndpointTable = $this->app->make(SiteEndpointTable::class, [
        'site' => Site::factory()->make(),
        'reportedSiteEndpoints' => new SiteEndpointCollection([$downSiteEndpoint, $upSiteEndpoint]),
        'inactiveSiteEndpointChecks' => new SiteEndpointCheckCollection(),
    ]);

    expect($siteEndpointTable->downSiteEndpoints->sole())
        ->toBe($downSiteEndpoint);
});

test('not down site endpoints are the endpoints whose latest check did not fail', function (): void {
    $downSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN]));
    $upSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP]));

    $siteEndpointTable = $this->app->make(SiteEndpointTable::class, [
        'site' => Site::factory()->make(),
        'reportedSiteEndpoints' => new SiteEndpointCollection([$downSiteEndpoint, $upSiteEndpoint]),
        'inactiveSiteEndpointChecks' => new SiteEndpointCheckCollection(),
    ]);

    expect($siteEndpointTable->notDownSiteEndpoints->sole())
        ->toBe($upSiteEndpoint);
});

test('down site endpoint count counts the endpoints whose latest check failed', function (): void {
    $reportedSiteEndpoints = new SiteEndpointCollection([
        SiteEndpoint::factory()
            ->make()
            ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN])),
        SiteEndpoint::factory()
            ->make()
            ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP])),
        SiteEndpoint::factory()
            ->make()
            ->setRelation('latestCheck', null),
    ]);

    $siteEndpointTable = $this->app->make(SiteEndpointTable::class, [
        'site' => Site::factory()->make(),
        'reportedSiteEndpoints' => $reportedSiteEndpoints,
        'inactiveSiteEndpointChecks' => new SiteEndpointCheckCollection(),
    ]);

    expect($siteEndpointTable->downSiteEndpointCount)
        ->toBe(1);
});

test('up site endpoint count counts the endpoints whose latest check succeeded', function (): void {
    $reportedSiteEndpoints = new SiteEndpointCollection([
        SiteEndpoint::factory()
            ->make()
            ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN])),
        SiteEndpoint::factory()
            ->make()
            ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP])),
        SiteEndpoint::factory()
            ->make()
            ->setRelation('latestCheck', null),
    ]);

    $siteEndpointTable = $this->app->make(SiteEndpointTable::class, [
        'site' => Site::factory()->make(),
        'reportedSiteEndpoints' => $reportedSiteEndpoints,
        'inactiveSiteEndpointChecks' => new SiteEndpointCheckCollection(),
    ]);

    expect($siteEndpointTable->upSiteEndpointCount)
        ->toBe(1);
});

test('render renders the site endpoint table view', function (): void {
    $siteEndpointTable = $this->app->make(SiteEndpointTable::class, [
        'site' => Site::factory()->make(),
        'reportedSiteEndpoints' => new SiteEndpointCollection(),
        'inactiveSiteEndpointChecks' => new SiteEndpointCheckCollection(),
    ]);

    expect($siteEndpointTable->render()->name())
        ->toBe('compass::components.site-endpoint-table');
});
