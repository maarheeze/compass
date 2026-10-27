<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run with the collection of the sites', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncForgeCommand::class)->assertSuccessful();

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch): bool {
        return $pendingBatch->hasJobs([CollectSites::class]);
    });
});

test('handle records the run as a forge sync', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->artisan(SyncForgeCommand::class)->assertSuccessful();
    $busFake->dispatchedBatches()[0]->beforeCallbacks()[0](
        new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now()),
    );

    expect(Run::query()->sole()->type)
        ->toBe(RunType::FORGE_SYNC);
});
