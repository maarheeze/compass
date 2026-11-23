<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Jobs\InspectRequestingSite;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('handle starts a site inspection run of the site with the key for each source of its reports', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $key = fake()->sha1();
    $site = Site::factory()->create(['key' => $key]);
    $siteReports = SiteReport::factory()
        ->count(2)
        ->create(['site_id' => $site->id]);

    $inspectRequestingSite = new InspectRequestingSite($key);
    $this->app->call([$inspectRequestingSite, 'handle']);

    $inspectedSources = Run::query()
        ->where('subject_type', $site->getMorphClass())
        ->where('subject_id', $site->id)
        ->where('collector', CollectorType::SITE_REPORTS)
        ->pluck('source');

    expect($inspectedSources->all())
        ->toEqualCanonicalizing($siteReports->pluck('source')->all());
});

test('handle starts nothing when no site has the key', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    SiteReport::factory()->create();

    $inspectRequestingSite = new InspectRequestingSite(fake()->sha1());
    $this->app->call([$inspectRequestingSite, 'handle']);

    $busFake->assertNothingDispatched();
});
