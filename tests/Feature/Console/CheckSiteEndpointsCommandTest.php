<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run with the check of the site endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(CheckSiteEndpointsCommand::class)->assertSuccessful();

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch): bool {
        return $pendingBatch->hasJobs([CheckSiteEndpoints::class]);
    });
});

test('handle records the run as an endpoint check', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(CheckSiteEndpointsCommand::class)->assertSuccessful();
    $busFake->dispatchedBatches()[0]->beforeCallbacks()[0](
        new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now()),
    );

    expect(Run::query()->sole()->type)
        ->toBe(RunType::ENDPOINT_CHECK);
});
