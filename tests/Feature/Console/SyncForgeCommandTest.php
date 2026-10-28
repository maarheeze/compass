<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Bus\ChainedBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run with the collection of the sites', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncForgeCommand::class)->assertSuccessful();

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CollectSites;
        }),
        FinishRun::class,
    ]);
});

test('handle records the run as a forge sync', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncForgeCommand::class)->assertSuccessful();
    $busFake
        ->dispatched(ChainedBatch::class)
        ->sole()
        ->toPendingBatch()
        ->beforeCallbacks()[0](new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now()));

    expect(Run::query()->sole()->type)
        ->toBe(RunType::FORGE_SYNC);
});

test('handle finishes the run with forge run succeeded', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncForgeCommand::class)->assertSuccessful();

    $busFake->assertChained([
        ChainedBatch::class,
        static function (FinishRun $finishRun): bool {
            return $finishRun->runSucceeded instanceof ForgeRunSucceeded
                && $finishRun->runSucceeded->runId->equals($finishRun->runId);
        },
    ]);
});
