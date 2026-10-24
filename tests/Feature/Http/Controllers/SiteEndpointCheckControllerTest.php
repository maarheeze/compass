<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index contains the checks of the endpoint', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $endpointPath = new EndpointPath($siteEndpointCheck->path);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS, [$siteEndpointCheck->site, $endpointPath->routeKey()]));

    $response->assertViewHas('siteEndpointChecks', static function (LengthAwarePaginator $viewSiteEndpointChecks) use ($siteEndpointCheck): bool {
        return $viewSiteEndpointChecks->sole()->is($siteEndpointCheck);
    });
});

test('index leaves out the checks of another path', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->unique()->slug()));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS, [$siteEndpointCheck->site, $endpointPath->routeKey()]));

    $response->assertViewHas('siteEndpointChecks', static function (LengthAwarePaginator $viewSiteEndpointChecks): bool {
        return $viewSiteEndpointChecks->isEmpty();
    });
});

test('index leaves out the checks of another site', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $endpointPath = new EndpointPath($siteEndpointCheck->path);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS, [Site::factory()->create(), $endpointPath->routeKey()]));

    $response->assertViewHas('siteEndpointChecks', static function (LengthAwarePaginator $viewSiteEndpointChecks): bool {
        return $viewSiteEndpointChecks->isEmpty();
    });
});

test('index is not found for a path that is not encoded', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE_ENDPOINT_CHECKS, [$site, '!']));

    $response->assertNotFound();
});
