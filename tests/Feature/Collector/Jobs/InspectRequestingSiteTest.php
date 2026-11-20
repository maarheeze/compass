<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Jobs\InspectRequestingSite;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('handle queues the inspection of the site with the key for each source of its reports', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $key = fake()->sha1();
    $site = Site::factory()->create(['key' => $key]);
    $siteReports = SiteReport::factory()
        ->count(2)
        ->create(['site_id' => $site->id]);

    $inspectRequestingSite = new InspectRequestingSite($key);
    $this->app->call([$inspectRequestingSite, 'handle']);

    $inspectedSources = $busFake
        ->dispatched(InspectSite::class, static function (InspectSite $inspectSite) use ($site): bool {
            return $inspectSite->site->id->equals($site->id);
        })
        ->map(static function (InspectSite $inspectSite): string {
            return $inspectSite->source;
        });

    expect($inspectedSources->all())
        ->toEqualCanonicalizing($siteReports->pluck('source')->all());
});

test('handle queues nothing when no site has the key', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    SiteReport::factory()->create();

    $inspectRequestingSite = new InspectRequestingSite(fake()->sha1());
    $this->app->call([$inspectRequestingSite, 'handle']);

    $busFake->assertNothingDispatched();
});
