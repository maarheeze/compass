<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

test('not done keeps an open alert', function (): void {
    $openAlert = Alert::factory()
        ->active()
        ->create();

    $notDoneAlerts = Alert::query()
        ->notDone()
        ->get();

    expect($notDoneAlerts->sole()->is($openAlert))
        ->toBeTrue();
});

test('not done leaves out a done alert', function (): void {
    Alert::factory()
        ->done()
        ->create();

    $notDoneAlerts = Alert::query()
        ->notDone()
        ->get();

    expect($notDoneAlerts)
        ->toBeEmpty();
});

test('of site keeps an alert of the site', function (): void {
    $site = Site::factory()->create();
    $siteAlert = Alert::factory()
        ->debugEnabled()
        ->for($site, 'subject')
        ->create();

    $siteAlerts = Alert::query()
        ->ofSite($site)
        ->get();

    expect($siteAlerts->sole()->is($siteAlert))
        ->toBeTrue();
});

test('of site keeps an alert of an endpoint of the site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointAlert = Alert::factory()
        ->downSiteEndpoint()
        ->for($siteEndpoint, 'subject')
        ->create();

    $siteAlerts = Alert::query()
        ->ofSite($siteEndpoint->site)
        ->get();

    expect($siteAlerts->sole()->is($siteEndpointAlert))
        ->toBeTrue();
});

test('of site leaves out the alerts of another site and its endpoints', function (): void {
    Alert::factory()
        ->debugEnabled()
        ->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->create();

    $siteAlerts = Alert::query()
        ->ofSite(Site::factory()->create())
        ->get();

    expect($siteAlerts)
        ->toBeEmpty();
});
