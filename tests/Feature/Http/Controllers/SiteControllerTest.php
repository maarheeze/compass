<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Alerts\Types\SiteMissing;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Http\SiteFilter;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Filter;

test('index contains the sites', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITES));

    $response->assertViewHas('sites', static function (SiteCollection $viewSites) use ($site): bool {
        return $viewSites->sole()->is($site);
    });
});

test('index on a server contains only the sites on that server', function (): void {
    $site = Site::factory()->create(['server_name' => fake()->unique()->slug()]);
    Site::factory()->create(['server_name' => fake()->unique()->slug()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITES, [SiteFilter::SERVER_PARAMETER => $site->server_name]));

    $response->assertViewHas('sites', static function (SiteCollection $viewSites) use ($site): bool {
        return $viewSites->sole()->is($site);
    });
});

test('index on a php version contains only the sites on that php version', function (): void {
    $site = Site::factory()->create(['php_version' => 'php84']);
    Site::factory()->create(['php_version' => 'php83']);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITES, [SiteFilter::PHP_VERSION_PARAMETER => 'php84']));

    $response->assertViewHas('sites', static function (SiteCollection $viewSites) use ($site): bool {
        return $viewSites->sole()->is($site);
    });
});

test('index contains the server filter of the site filter', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITES, [SiteFilter::SERVER_PARAMETER => fake()->unique()->slug()]));

    $response->assertViewHas('serverFilter', static function (Filter $viewServerFilter) use ($site): bool {
        return $viewServerFilter->options->sole()->queryParameters === [SiteFilter::SERVER_PARAMETER => $site->server_name];
    });
});

test('index contains the php version filter of the site filter', function (): void {
    Site::factory()->create(['php_version' => 'php84']);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITES, [SiteFilter::PHP_VERSION_PARAMETER => 'php83']));

    $response->assertViewHas('phpVersionFilter', static function (Filter $viewPhpVersionFilter): bool {
        return $viewPhpVersionFilter->options->sole()->queryParameters === [SiteFilter::PHP_VERSION_PARAMETER => 'php84'];
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

test('show contains the checks of the site that apply', function (): void {
    $site = Site::factory()->create();
    $applicableAlertCheck = AlertCheck::factory()
        ->for($site, 'subject')
        ->create([
            'alert_type_identifier' => new SiteMissing()->identifier(),
            'condition' => fake()->randomElement([ConditionState::ACTIVE, ConditionState::RESOLVED]),
        ]);
    AlertCheck::factory()
        ->for($site, 'subject')
        ->create([
            'alert_type_identifier' => new SiteBehind()->identifier(),
            'condition' => ConditionState::NOT_APPLICABLE,
        ]);
    AlertCheck::factory()
        ->for(Site::factory(), 'subject')
        ->create(['condition' => ConditionState::ACTIVE]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $site));

    $response->assertViewHas(
        'alertChecks',
        static function (AlertCheckCollection $viewAlertChecks) use ($applicableAlertCheck): bool {
            return $viewAlertChecks->sole()->is($applicableAlertCheck);
        },
    );
});

test('show contains the checks of the reports of the site that apply', function (): void {
    $siteReport = SiteReport::factory()->create();
    $applicableAlertCheck = AlertCheck::factory()
        ->for($siteReport, 'subject')
        ->create(['condition' => fake()->randomElement([ConditionState::ACTIVE, ConditionState::RESOLVED])]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $siteReport->site));

    $response->assertViewHas(
        'alertChecks',
        static function (AlertCheckCollection $viewAlertChecks) use ($applicableAlertCheck): bool {
            return $viewAlertChecks->sole()->is($applicableAlertCheck);
        },
    );
});

test('show contains the reported endpoints of the site with their latest check', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create([
        'site_id' => $siteEndpoint->siteReport->site_id,
        'path' => $siteEndpoint->path,
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE, $siteEndpoint->siteReport->site_id));

    $response->assertViewHas(
        'reportedSiteEndpoints',
        static function (
            SiteEndpointCollection $viewReportedSiteEndpoints,
        ) use (
            $siteEndpoint,
            $siteEndpointCheck,
        ): bool {
            $viewReportedSiteEndpoint = $viewReportedSiteEndpoints->sole();

            return $viewReportedSiteEndpoint->is($siteEndpoint)
                && $viewReportedSiteEndpoint->latestCheck?->is($siteEndpointCheck) === true;
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
