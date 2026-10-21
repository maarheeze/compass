<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Console\SyncForgeCommand;

test('handle starts a collection of the sites', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $this->artisan(SyncForgeCommand::class)->assertSuccessful();

    expect($busFake->dispatched(CollectSites::class))
        ->toHaveCount(1);
});
