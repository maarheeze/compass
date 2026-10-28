<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\DownSiteEndpointAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

test('description names the path and the site of the endpoint', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->downSiteEndpoint()
        ->for($siteEndpoint, 'subject')
        ->create();

    $downSiteEndpointAlert = $this->app->make(DownSiteEndpointAlert::class);
    $description = $downSiteEndpointAlert->description($alert);

    expect($description)
        ->toBe(sprintf('%s on %s', $siteEndpoint->path, $siteEndpoint->site->name));
});
