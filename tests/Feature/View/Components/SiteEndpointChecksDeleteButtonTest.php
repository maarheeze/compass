<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteEndpointChecksDeleteButton;

test('delete url deletes the checks of the endpoint of the site', function (): void {
    $site = Site::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->slug()));

    $siteEndpointChecksDeleteButton = $this->app->make(SiteEndpointChecksDeleteButton::class, [
        'site' => $site,
        'path' => $endpointPath,
    ]);

    expect($siteEndpointChecksDeleteButton->deleteUrl)
        ->toBe(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS_DELETE, [$site, $endpointPath->routeKey()]));
});
