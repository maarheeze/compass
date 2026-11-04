<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Collector\Forge\MissingSiteResolver;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

test('can ignore is true for a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $canIgnore = $missingSiteResolver->canIgnore($site);

    expect($canIgnore)
        ->toBeTrue();
});

test('can ignore is false for a collected site', function (): void {
    $site = Site::factory()->create();

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $canIgnore = $missingSiteResolver->canIgnore($site);

    expect($canIgnore)
        ->toBeFalse();
});

test('can ignore is false for a site that is already ignored', function (): void {
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $canIgnore = $missingSiteResolver->canIgnore($site);

    expect($canIgnore)
        ->toBeFalse();
});

test('delete deletes a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->delete($site);

    $this->assertModelMissing($site);
});

test('delete deletes the alerts of the missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $alert = Alert::factory()
        ->siteMissing()
        ->for($site, 'subject')
        ->create();

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->delete($site);

    $this->assertModelMissing($alert);
});

test('delete deletes the alerts of the endpoints of the missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->for($site))
        ->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->delete($site);

    $this->assertModelMissing($alert);
});

test('delete keeps the alerts of another site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $otherAlert = Alert::factory()
        ->siteMissing()
        ->create();

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->delete($site);

    $this->assertModelExists($otherAlert);
});

test('delete refuses a collected site', function (): void {
    $site = Site::factory()->create();

    $this->expectException(ModelNotFoundException::class);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->delete($site);
});

test('ignore marks a missing site ignored', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->ignore($site);

    expect($site->refresh()->missing_ignored_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('ignore raises site missing ignored with the site', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->ignore($site);

    $eventFake->assertDispatched(SiteMissingIgnored::class, static function (SiteMissingIgnored $siteMissingIgnored) use ($site): bool {
        return $siteMissingIgnored->site->is($site);
    });
});

test('ignore refuses a collected site', function (): void {
    $site = Site::factory()->create();

    $this->expectException(ModelNotFoundException::class);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->ignore($site);
});

test('ignore refuses a site that is already ignored', function (): void {
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $this->expectException(ModelNotFoundException::class);

    $missingSiteResolver = $this->app->make(MissingSiteResolver::class);
    $missingSiteResolver->ignore($site);
});
