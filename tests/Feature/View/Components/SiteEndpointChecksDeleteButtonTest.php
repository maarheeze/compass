<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Http\EndpointPathRouteParameter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteEndpointChecksDeleteButton;

test('delete url deletes the checks of the endpoint of the site', function (): void {
    $site = Site::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->slug()));
    $endpointPathRouteParameter = $this->app->make(EndpointPathRouteParameter::class);

    $siteEndpointChecksDeleteButton = $this->app->make(SiteEndpointChecksDeleteButton::class, [
        'site' => $site,
        'path' => $endpointPath,
    ]);

    expect($siteEndpointChecksDeleteButton->deleteUrl)
        ->toBe(route(RouteName::SITE_ENDPOINT_CHECKS_DELETE, [$site, $endpointPathRouteParameter->encode($endpointPath)]));
});
