<?php

declare(strict_types=1);

use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Jobs\SyncJob;
use Maarheeze\Compass\Collector\Listeners\RecordRunFailure;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
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
        'messages' => json_encode([$exception->getMessage()]),
    ]);
});

test('handle records the messages of the exceptions behind a failed job in a run', function (): void {
    $run = Run::factory()->create();
    $previousException = new RuntimeException(fake()->sentence());
    $exception = new LogicException(fake()->sentence(), 0, $previousException);
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
        'messages' => json_encode([$exception->getMessage(), $previousException->getMessage()]),
    ]);
});

test('handle leaves an empty exception message out of a failed job in a run', function (): void {
    $run = Run::factory()->create();
    $previousException = new RuntimeException(fake()->sentence());
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => $run->batch_id]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, new LogicException('', 0, $previousException)));

    $this->assertDatabaseHas(RunFailure::class, [
        'run_id' => $run->id,
        'messages' => json_encode([$previousException->getMessage()]),
    ]);
});

test('handle leaves a repeated exception message out of a failed job in a run', function (): void {
    $run = Run::factory()->create();
    $exception = new LogicException(fake()->sentence());
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => $run->batch_id]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(
        fake()->word(),
        $syncJob,
        new RuntimeException($exception->getMessage(), 0, $exception),
    ));

    $this->assertDatabaseHas(RunFailure::class, [
        'run_id' => $run->id,
        'messages' => json_encode([$exception->getMessage()]),
    ]);
});

test('handle records the log context values of a failed job in a run in the order they were added', function (): void {
    $run = Run::factory()->create();
    $repositoryName = fake()->slug();
    $fileName = fake()->word();
    $context = $this->app->make(Context::class);
    $context->add('repository', $repositoryName);
    $context->add('file', $fileName);
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => $run->batch_id]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, new LogicException(fake()->sentence())));

    $this->assertDatabaseHas(RunFailure::class, [
        'run_id' => $run->id,
        'context' => json_encode([$repositoryName, $fileName]),
    ]);
});

test('handle leaves the run id out of the log context of a failed job in a run', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => $run->batch_id]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, new LogicException(fake()->sentence())));

    $this->assertDatabaseHas(RunFailure::class, [
        'run_id' => $run->id,
        'context' => json_encode([]),
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

test('handle leaves a failed job of one subject to its run result', function (): void {
    $run = Run::factory()->create();
    $syncJob = new SyncJob(
        $this->app,
        json_encode(['data' => ['batchId' => $run->batch_id, 'commandName' => InspectSite::class]]),
        fake()->word(),
        fake()->word(),
    );

    $recordRunFailure = $this->app->make(RecordRunFailure::class);
    $recordRunFailure->handle(new JobFailed(fake()->word(), $syncJob, new LogicException(fake()->sentence())));

    $this->assertDatabaseEmpty(RunFailure::class);
});
