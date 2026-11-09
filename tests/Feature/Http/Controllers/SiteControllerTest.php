<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index contains the sites', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITES));

    $response->assertViewHas('sites', static function (SiteCollection $viewSites) use ($site): bool {
        return $viewSites->sole()->is($site);
    });
});

test('show contains the site', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $site));

    $response->assertViewHas('site', static function (Site $viewSite) use ($site): bool {
        return $viewSite->is($site);
    });
});

test('show contains the alerts of the site and its endpoints, newest first', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $earlierAlert = Alert::factory()
        ->siteDebugEnabled()
        ->done()
        ->for($siteEndpoint->siteReport, 'subject')
        ->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterAlert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create(['created_at' => fake()->dateTimeBetween('-1 month')]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $siteEndpoint->siteReport->site));

    $response->assertViewHas('alerts', static function (AlertCollection $alerts) use ($earlierAlert, $laterAlert): bool {
        return $alerts->count() === 2
            && $alerts->first()->id->equals($laterAlert->id)
            && $alerts->last()->id->equals($earlierAlert->id);
    });
});

test('show loads the assignee of the alerts', function (): void {
    $alert = Alert::factory()
        ->siteMissing()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $alert->subject_id));

    $response->assertViewHas('alerts', static function (AlertCollection $alerts): bool {
        return $alerts->sole()->relationLoaded('assignee');
    });
});

test('show contains the latest check of a reported endpoint path as active', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $activeSiteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->siteReport->site_id,
        'path' => new EndpointPath($siteEndpoint->path),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $siteEndpoint->siteReport->site_id));

    $response->assertViewHas(
        'activeSiteEndpointChecks',
        static function (
            SiteEndpointCheckCollection $viewActiveSiteEndpointChecks,
        ) use ($activeSiteEndpointCheck): bool {
            return $viewActiveSiteEndpointChecks->sole()->is($activeSiteEndpointCheck);
        },
    );
});

test('show contains the latest check of an endpoint path no longer reported as inactive', function (): void {
    $inactiveSiteEndpointCheck = SiteEndpointCheck::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $inactiveSiteEndpointCheck->site_id));

    $response->assertViewHas(
        'inactiveSiteEndpointChecks',
        static function (
            SiteEndpointCheckCollection $viewInactiveSiteEndpointChecks,
        ) use ($inactiveSiteEndpointCheck): bool {
            return $viewInactiveSiteEndpointChecks->sole()->is($inactiveSiteEndpointCheck);
        },
    );
});

test('show renders a site report whose source is not configured', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $siteReport->site));

    $response->assertOk();
});

test('delete deletes a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->delete(route(RouteName::SITE_DELETE, $site));

    $this->assertModelMissing($site);
});

test('delete sends the user back to the dashboard', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::SITE_DELETE, $site));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('delete is not found for a collected site', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->delete(route(RouteName::SITE_DELETE, $site));

    $response->assertNotFound();
});

test('ignore marks a missing site ignored', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::SITE_IGNORE, $site));

    expect($site->refresh()->missing_ignored_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('ignore sends the user back to the dashboard', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::SITE_IGNORE, $site));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});
