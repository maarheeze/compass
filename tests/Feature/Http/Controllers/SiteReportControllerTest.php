<?php

declare(strict_types=1);

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('edit contains the site report', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE_REPORT_EDIT, $siteReport));

    $response->assertViewHas('siteReport', static function (SiteReport $viewSiteReport) use ($siteReport): bool {
        return $viewSiteReport->is($siteReport);
    });
});

test('update stores the path and key of the site report', function (): void {
    $siteReport = SiteReport::factory()->create();
    $path = sprintf('/%s', fake()->slug());
    $key = fake()->sha1();
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport), [
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
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport), [
        'path' => '',
        'key' => '',
    ]);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'path' => null,
        'key' => null,
    ]);
});

test('update starts a site reports run of the source of the site report', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport), []);

    $this->assertDatabaseHas(Run::class, [
        'source' => $siteReport->source,
        'collector' => CollectorType::SITE_REPORTS,
    ]);
});

test('update runs an inspection of the site for the source of the site report', function (): void {
    $siteReport = SiteReport::factory()->create();
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport), []);

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($siteReport): bool {
            $inspectSite = $pendingBatch->jobs->sole();

            return $inspectSite instanceof InspectSite
                && $inspectSite->site->is($siteReport->site)
                && $inspectSite->sourceKey === $siteReport->source;
        }),
        FinishRun::class,
    ]);
});

test('update refuses a path without a leading slash', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(
        route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport),
        ['path' => fake()->slug()],
    );

    $response->assertSessionHasErrors('path');
});

test('update sends the user back to the site', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport), []);

    $response->assertRedirect(route(RouteName::FORGE_SITE, $siteReport->site));
});

test('edit contains the path that was entered before a refusal', function (): void {
    $siteReport = SiteReport::factory()->create();
    $enteredPath = fake()->slug();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->withSession(['_old_input' => ['path' => $enteredPath]])
        ->get(route(RouteName::FORGE_SITE_REPORT_EDIT, $siteReport));

    $response->assertViewHas('path', $enteredPath);
});

test('edit shows why a path was refused', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->followingRedirects()
        ->from(route(RouteName::FORGE_SITE_REPORT_EDIT, $siteReport))
        ->patch(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport), ['path' => fake()->slug()]);

    $response->assertSeeText('The path must start with a /.');
});
