<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\View\Components\AlertSubjectLink;

test('url is the compass page of a repository subject', function (): void {
    $repository = Repository::factory()->create();

    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => $repository]);

    expect($alertSubjectLink->url)
        ->toBe(route(RouteName::GITHUB_REPOSITORY, $repository));
});

test('url is the compass page of a site subject', function (): void {
    $site = Site::factory()->create();

    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => $site]);

    expect($alertSubjectLink->url)
        ->toBe(route(RouteName::FORGE_SITE, $site));
});

test('name is the display name of the subject', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => $siteEndpoint]);

    expect($alertSubjectLink->name)
        ->toBe($siteEndpoint->displayName());
});

test('url is the check history of a site endpoint subject', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => $siteEndpoint]);

    expect($alertSubjectLink->url)
        ->toBe(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS, [
            $siteEndpoint->siteReport->site,
            new EndpointPath($siteEndpoint->path)->routeKey(),
        ]));
});

test('render renders the alert subject link view', function (): void {
    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => Site::factory()->create()]);

    expect($alertSubjectLink->render()->name())
        ->toBe('compass::components.alert-subject-link');
});
