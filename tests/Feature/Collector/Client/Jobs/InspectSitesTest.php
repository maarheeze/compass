<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Models\Site;

test('handle inspects a collected site', function (): void {
    $site = Site::factory()->create();
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $inspectSites = new InspectSites();
    $this->app->call([$inspectSites, 'handle']);

    $busFake->assertDispatched(
        InspectSite::class,
        static function (InspectSite $inspectSite) use ($site): bool {
            return $inspectSite->site->is($site);
        },
    );
});

test('handle leaves out a missing site', function (): void {
    Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $inspectSites = new InspectSites();
    $this->app->call([$inspectSites, 'handle']);

    expect($busFake->dispatched(InspectSite::class))
        ->toBeEmpty();
});
