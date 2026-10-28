<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Bus\ChainedBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Events\EndpointCheckRunSucceeded;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run with the check of the site endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(CheckSiteEndpointsCommand::class)->assertSuccessful();

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CheckSiteEndpoints;
        }),
        FinishRun::class,
    ]);
});

test('handle records the run as an endpoint check', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(CheckSiteEndpointsCommand::class)->assertSuccessful();
    $busFake
        ->dispatched(ChainedBatch::class)
        ->sole()
        ->toPendingBatch()
        ->beforeCallbacks()[0](new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now()));

    expect(Run::query()->sole()->type)
        ->toBe(RunType::ENDPOINT_CHECK);
});

test('handle finishes the run with endpoint check run succeeded', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(CheckSiteEndpointsCommand::class)->assertSuccessful();

    $busFake->assertChained([
        ChainedBatch::class,
        static function (FinishRun $finishRun): bool {
            return $finishRun->runSucceeded instanceof EndpointCheckRunSucceeded
                && $finishRun->runSucceeded->runId->equals($finishRun->runId);
        },
    ]);
});
