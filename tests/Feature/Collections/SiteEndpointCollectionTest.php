<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('down is the endpoints whose latest check failed', function (): void {
    $downSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN]));
    $upSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP]));
    $uncheckedSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', null);
    $siteEndpoints = new SiteEndpointCollection([$downSiteEndpoint, $upSiteEndpoint, $uncheckedSiteEndpoint]);

    $downSiteEndpoints = $siteEndpoints->down();

    expect($downSiteEndpoints->sole())
        ->toBe($downSiteEndpoint);
});

test('up is the endpoints whose latest check succeeded', function (): void {
    $downSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN]));
    $upSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP]));
    $uncheckedSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', null);
    $siteEndpoints = new SiteEndpointCollection([$downSiteEndpoint, $upSiteEndpoint, $uncheckedSiteEndpoint]);

    $upSiteEndpoints = $siteEndpoints->up();

    expect($upSiteEndpoints->sole())
        ->toBe($upSiteEndpoint);
});

test('not down is the endpoints whose latest check succeeded or that are not checked yet', function (): void {
    $downSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::DOWN]));
    $upSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', SiteEndpointCheck::factory()->make(['state' => SiteEndpointState::UP]));
    $uncheckedSiteEndpoint = SiteEndpoint::factory()
        ->make()
        ->setRelation('latestCheck', null);
    $siteEndpoints = new SiteEndpointCollection([$downSiteEndpoint, $upSiteEndpoint, $uncheckedSiteEndpoint]);

    $notDownSiteEndpoints = $siteEndpoints->notDown();

    expect($notDownSiteEndpoints->values()->all())
        ->toBe([$upSiteEndpoint, $uncheckedSiteEndpoint]);
});
