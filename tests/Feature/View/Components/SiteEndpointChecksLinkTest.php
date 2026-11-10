<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Http\EndpointPathRouteParameter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteEndpointChecksLink;

test('url is the checks page of the endpoint of the site', function (): void {
    $site = Site::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->slug()));
    $endpointPathRouteParameter = $this->app->make(EndpointPathRouteParameter::class);

    $siteEndpointChecksLink = $this->app->make(SiteEndpointChecksLink::class, [
        'site' => $site,
        'path' => $endpointPath,
    ]);

    expect($siteEndpointChecksLink->url)
        ->toBe(route(RouteName::SITE_ENDPOINT_CHECKS, [$site, $endpointPathRouteParameter->encode($endpointPath)]));
});
