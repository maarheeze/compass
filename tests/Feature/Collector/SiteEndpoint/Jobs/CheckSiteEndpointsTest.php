<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoint;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

test('handle checks an endpoint of a collected site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $checkSiteEndpoints = new CheckSiteEndpoints();
    $this->app->call([$checkSiteEndpoints, 'handle']);

    $busFake->assertDispatched(
        CheckSiteEndpoint::class,
        static function (CheckSiteEndpoint $checkSiteEndpoint) use ($siteEndpoint): bool {
            return $checkSiteEndpoint->siteEndpoint->is($siteEndpoint);
        },
    );
});

test('handle leaves out the endpoints of a missing site', function (): void {
    SiteEndpoint::factory()
        ->for(Site::factory()->state(['missing_at' => fake()->dateTime()]))
        ->create();
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $checkSiteEndpoints = new CheckSiteEndpoints();
    $this->app->call([$checkSiteEndpoints, 'handle']);

    expect($busFake->dispatched(CheckSiteEndpoint::class))
        ->toBeEmpty();
});
