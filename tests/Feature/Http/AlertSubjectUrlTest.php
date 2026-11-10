<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\AlertSubjectUrl;
use Maarheeze\Compass\Http\EndpointPathRouteParameter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

test('url is the compass page of a repository', function (): void {
    $repository = Repository::factory()->create();

    $alertSubjectUrl = $this->app->make(AlertSubjectUrl::class);
    $url = $alertSubjectUrl->url($repository);

    expect($url)
        ->toBe(route(RouteName::REPOSITORY, $repository));
});

test('url is the compass page of a site', function (): void {
    $site = Site::factory()->create();

    $alertSubjectUrl = $this->app->make(AlertSubjectUrl::class);
    $url = $alertSubjectUrl->url($site);

    expect($url)
        ->toBe(route(RouteName::SITE, $site));
});

test('url is the check history of a site endpoint', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $endpointPathRouteParameter = $this->app->make(EndpointPathRouteParameter::class);

    $alertSubjectUrl = $this->app->make(AlertSubjectUrl::class);
    $url = $alertSubjectUrl->url($siteEndpoint);

    expect($url)
        ->toBe(route(RouteName::SITE_ENDPOINT_CHECKS, [
            $siteEndpoint->siteReport->site,
            $endpointPathRouteParameter->encode($siteEndpoint->path),
        ]));
});

test('url is the compass page of the site of a site report', function (): void {
    $siteReport = SiteReport::factory()->create();

    $alertSubjectUrl = $this->app->make(AlertSubjectUrl::class);
    $url = $alertSubjectUrl->url($siteReport);

    expect($url)
        ->toBe(route(RouteName::SITE, $siteReport->site));
});
