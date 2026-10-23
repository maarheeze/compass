<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
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

test('show contains the key of the site', function (): void {
    $site = Site::factory()->create(['client_key' => fake()->sha1()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE, $site));

    $response->assertViewHas('clientKey', $site->client_key);
});

test('show contains the path of the site', function (): void {
    $site = Site::factory()->create(['client_path' => sprintf('/%s', fake()->slug())]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE, $site));

    $response->assertViewHas('clientPath', $site->client_path);
});

test('delete deletes a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::FORGE_SITE_DELETE, $site));

    $this->assertModelMissing($site);
});

test('delete sends the user back to the dashboard', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::FORGE_SITE_DELETE, $site));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('delete is not found for a collected site', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::FORGE_SITE_DELETE, $site));

    $response->assertNotFound();
});

test('ignore marks a missing site ignored', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::FORGE_SITE_IGNORE, $site));

    expect($site->refresh()->missing_ignored_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('ignore sends the user back to the dashboard', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::FORGE_SITE_IGNORE, $site));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});
