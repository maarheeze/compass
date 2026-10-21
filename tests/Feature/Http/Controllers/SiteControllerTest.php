<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index contains the sites', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITES));

    $response->assertViewHas('sites', static function (SiteCollection $viewSites) use ($site): bool {
        return $viewSites->sole()->is($site);
    });
});

test('show contains the site', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE, $site));

    $response->assertViewHas('site', static function (Site $viewSite) use ($site): bool {
        return $viewSite->is($site);
    });
});
