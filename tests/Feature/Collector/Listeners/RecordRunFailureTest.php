<?php

declare(strict_types=1);

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Jobs\SyncJob;
use Maarheeze\Compass\Collector\Listeners\RecordRunFailure;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunFailure;

test('handle records the exception message of a failed job in a run', function (): void {
    $run = Run::factory()->create();
    $exception = new LogicException(fake()->sentence());
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => $run->batch_id]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, $exception));

    $this->assertDatabaseHas(RunFailure::class, [
        'run_id' => $run->id,
        'message' => $exception->getMessage(),
    ]);
});

test('handle ignores a failed job outside a batch', function (): void {
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => null]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, new LogicException(fake()->sentence())));

    $this->assertDatabaseEmpty(RunFailure::class);
});

test('handle ignores a failed job in a batch of no run', function (): void {
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => fake()->uuid()]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, new LogicException(fake()->sentence())));

    $this->assertDatabaseEmpty(RunFailure::class);
});
