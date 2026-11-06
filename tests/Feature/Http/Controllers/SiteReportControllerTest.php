<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('edit contains the site report', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::SITE_REPORT_EDIT, $siteReport));

    $response->assertViewHas('siteReport', static function (SiteReport $viewSiteReport) use ($siteReport): bool {
        return $viewSiteReport->is($siteReport);
    });
});

test('update stores the path and key of the site report', function (): void {
    $siteReport = SiteReport::factory()->create();
    $path = sprintf('/%s', fake()->slug());
    $key = fake()->sha1();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::SITE_REPORT_UPDATE, $siteReport), [
        'path' => $path,
        'key' => $key,
    ]);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'path' => $path,
        'key' => $key,
    ]);
});

test('update clears the path and key of the site report when left empty', function (): void {
    $siteReport = SiteReport::factory()->create([
        'path' => sprintf('/%s', fake()->slug()),
        'key' => fake()->sha1(),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::SITE_REPORT_UPDATE, $siteReport), [
        'path' => '',
        'key' => '',
    ]);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'path' => null,
        'key' => null,
    ]);
});

test('update refuses a path without a leading slash', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(
        route(RouteName::SITE_REPORT_UPDATE, $siteReport),
        ['path' => fake()->slug()],
    );

    $response->assertSessionHasErrors('path');
});

test('update sends the user back to the site', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::SITE_REPORT_UPDATE, $siteReport), []);

    $response->assertRedirect(route(RouteName::SITE, $siteReport->site));
});

test('edit contains the path that was entered before a refusal', function (): void {
    $siteReport = SiteReport::factory()->create();
    $enteredPath = fake()->slug();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->withSession(['_old_input' => ['path' => $enteredPath]])
        ->get(route(RouteName::SITE_REPORT_EDIT, $siteReport));

    $response->assertViewHas('path', $enteredPath);
});

test('edit shows why a path was refused', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->followingRedirects()
        ->from(route(RouteName::SITE_REPORT_EDIT, $siteReport))
        ->patch(route(RouteName::SITE_REPORT_UPDATE, $siteReport), ['path' => fake()->slug()]);

    $response->assertSeeText('The path must start with a /.');
});
