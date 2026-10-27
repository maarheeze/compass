<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Console\SyncCommand;

test('handle starts the collection of the repositories', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncCommand::class)->assertSuccessful();

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch): bool {
        return $pendingBatch->hasJobs([CollectRepositories::class]);
    });
});

test('handle starts the collection of the sites', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncCommand::class)->assertSuccessful();

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch): bool {
        return $pendingBatch->hasJobs([CollectSites::class]);
    });
});

test('handle starts the check of the site endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncCommand::class)->assertSuccessful();

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch): bool {
        return $pendingBatch->hasJobs([CheckSiteEndpoints::class]);
    });
});
