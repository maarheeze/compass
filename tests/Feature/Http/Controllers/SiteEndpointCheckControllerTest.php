<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('delete deletes the checks of the endpoint', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::SITE_ENDPOINT_CHECKS_DELETE, [$siteEndpointCheck->site, $siteEndpointCheck->path->routeKey()]));

    $this->assertModelMissing($siteEndpointCheck);
});

test('delete leaves the checks of another path', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->unique()->slug()));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::SITE_ENDPOINT_CHECKS_DELETE, [$siteEndpointCheck->site, $endpointPath->routeKey()]));

    $this->assertModelExists($siteEndpointCheck);
});

test('delete leaves the checks of another site', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::SITE_ENDPOINT_CHECKS_DELETE, [Site::factory()->create(), $siteEndpointCheck->path->routeKey()]));

    $this->assertModelExists($siteEndpointCheck);
});

test('delete sends the user back to the site', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::SITE_ENDPOINT_CHECKS_DELETE, [$siteEndpointCheck->site, $siteEndpointCheck->path->routeKey()]));

    $response->assertRedirect(route(RouteName::SITE, $siteEndpointCheck->site));
});

test('index contains the checks of the endpoint', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE_ENDPOINT_CHECKS, [$siteEndpointCheck->site, $siteEndpointCheck->path->routeKey()]));

    $response->assertViewHas('siteEndpointChecks', static function (LengthAwarePaginator $viewSiteEndpointChecks) use ($siteEndpointCheck): bool {
        return $viewSiteEndpointChecks->sole()->is($siteEndpointCheck);
    });
});

test('index leaves out the checks of another path', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $endpointPath = new EndpointPath(sprintf('/%s', fake()->unique()->slug()));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE_ENDPOINT_CHECKS, [$siteEndpointCheck->site, $endpointPath->routeKey()]));

    $response->assertViewHas('siteEndpointChecks', static function (LengthAwarePaginator $viewSiteEndpointChecks): bool {
        return $viewSiteEndpointChecks->isEmpty();
    });
});

test('index leaves out the checks of another site', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE_ENDPOINT_CHECKS, [Site::factory()->create(), $siteEndpointCheck->path->routeKey()]));

    $response->assertViewHas('siteEndpointChecks', static function (LengthAwarePaginator $viewSiteEndpointChecks): bool {
        return $viewSiteEndpointChecks->isEmpty();
    });
});

test('index is not found for a path that is not encoded', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE_ENDPOINT_CHECKS, [$site, '!']));

    $response->assertNotFound();
});

test('index shows the configured number of checks per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);
    $this->setConfiguration('compass.items_per_page', $itemsPerPage);
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE_ENDPOINT_CHECKS, [
        $siteEndpointCheck->site,
        $siteEndpointCheck->path->routeKey(),
    ]));

    $response->assertViewHas(
        'siteEndpointChecks',
        static function (LengthAwarePaginator $viewSiteEndpointChecks) use ($itemsPerPage): bool {
            return $viewSiteEndpointChecks->perPage() === $itemsPerPage;
        },
    );
});
