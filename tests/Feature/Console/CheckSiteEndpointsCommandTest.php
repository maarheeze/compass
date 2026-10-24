<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;

test('handle dispatches the check of the site endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $this->artisan(CheckSiteEndpointsCommand::class)->assertSuccessful();

    $busFake->assertDispatched(CheckSiteEndpoints::class);
});
