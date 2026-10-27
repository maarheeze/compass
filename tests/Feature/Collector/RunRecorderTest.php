<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Models\Run;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

test('start dispatches the first job in a batch', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $firstJob = new InspectSites();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(RunId::generate(), fake()->randomElement(RunType::cases()), $firstJob);

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch) use ($firstJob): bool {
        return $pendingBatch->jobs->all() === [$firstJob];
    });
});

test('start keeps the batch going when one of its jobs fails', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(RunId::generate(), fake()->randomElement(RunType::cases()), new InspectSites());

    $busFake->assertBatched(static function (PendingBatchFake $pendingBatch): bool {
        return $pendingBatch->allowsFailures();
    });
});

test('start records the run of the type with its batch when the batch is stored', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = RunId::generate();
    $runType = fake()->randomElement(RunType::cases());
    $batchFake = new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start($runId, $runType, new InspectSites());
    $busFake->dispatchedBatches()[0]->beforeCallbacks()[0]($batchFake);

    $this->assertDatabaseHas(Run::class, [
        'id' => $runId,
        'type' => $runType,
        'batch_id' => $batchFake->id,
    ]);
});

test('start records the failed job count of the run when the batch finishes', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $run = Run::factory()->create();
    $failedJobCount = fake()->numberBetween(0, 10);
    $batchFake = new BatchFake($run->batch_id, '', 1, 0, $failedJobCount, [], [], CarbonImmutable::now());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start($run->id, $run->type, new InspectSites());
    $busFake->dispatchedBatches()[0]->finallyCallbacks()[0]($batchFake);

    $this->assertDatabaseHas(Run::class, [
        'id' => $run->id,
        'failed_job_count' => $failedJobCount,
    ]);
});

test('start records when the run finished when the batch finishes', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $run = Run::factory()->create();
    $batchFake = new BatchFake($run->batch_id, '', 1, 0, 0, [], [], CarbonImmutable::now());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start($run->id, $run->type, new InspectSites());
    $busFake->dispatchedBatches()[0]->finallyCallbacks()[0]($batchFake);

    expect($run->refresh()->finished_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('start logs the start of the run with its run id in the log context', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $channel = fake()->slug();
    $this->app->make(Repository::class)->set(sprintf('logging.channels.%s', $channel), [
        'driver' => 'monolog',
        'handler' => TestHandler::class,
        'level' => LogLevel::DEBUG,
    ]);
    $this->setConfiguration('compass.log_channel', $channel);
    $runId = RunId::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start($runId, fake()->randomElement(RunType::cases()), new InspectSites());

    $testHandler = $this->app
        ->make(LogManager::class)
        ->channel($channel)
        ->getLogger()
        ->getHandlers()[0];
    $hasLoggedRunStartWithRunId = $testHandler->hasInfoThatPasses(
        static function (LogRecord $logRecord) use ($runId): bool {
            return $logRecord->message === 'Run started' && $logRecord->extra['run_id'] === $runId->toString();
        },
    );

    expect($hasLoggedRunStartWithRunId)
        ->toBeTrue();
});
