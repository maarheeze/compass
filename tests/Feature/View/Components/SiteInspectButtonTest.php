<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteInspectButton;

test('inspect url is the inspect route of the site', function (): void {
    $site = Site::factory()->create();

    $siteInspectButton = $this->app->make(SiteInspectButton::class, ['site' => $site]);

    expect($siteInspectButton->inspectUrl)
        ->toBe(route(RouteName::SITE_INSPECT, $site));
});

test('should render for a site with a report', function (): void {
    $site = Site::factory()->create();
    SiteReport::factory()->create(['site_id' => $site->id]);

    $siteInspectButton = $this->app->make(SiteInspectButton::class, ['site' => $site]);

    expect($siteInspectButton->shouldRender())
        ->toBeTrue();
});

test('should not render for a site without a report', function (): void {
    $site = Site::factory()->create();

    $siteInspectButton = $this->app->make(SiteInspectButton::class, ['site' => $site]);

    expect($siteInspectButton->shouldRender())
        ->toBeFalse();
});

test('render renders the site inspect button view', function (): void {
    $site = Site::factory()->create();

    $siteInspectButton = $this->app->make(SiteInspectButton::class, ['site' => $site]);

    expect($siteInspectButton->render()->name())
        ->toBe('compass::components.site-inspect-button');
});
