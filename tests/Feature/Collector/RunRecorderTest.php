<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Bus\ChainedBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Uuid\Uuid;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

test('start collector chains the batch with the first job', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        $runId,
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
    );

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CheckSiteEndpoints;
        }),
        FinishRun::class,
    ]);
});

test('start collector keeps the batch going when one of its jobs fails', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        $runId,
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
    );

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->allowsFailures();
        }),
        FinishRun::class,
    ]);
});

test('start collector records the queued run of the collector of the source', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $runId = Uuid::generate();
    $sourceKey = fake()->slug();
    $collectorType = fake()->randomElement(CollectorType::cases());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector($runId, $sourceKey, $collectorType, new CheckSiteEndpoints());

    $this->assertDatabaseHas(Run::class, [
        'id' => $runId,
        'source' => $sourceKey,
        'collector' => $collectorType,
        'status' => RunStatus::QUEUED,
        'batch_id' => null,
    ]);
});

test('start collector records the run as running with its batch when the batch is stored', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();
    $batchFake = new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        $runId,
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
    );
    $busFake
        ->dispatched(ChainedBatch::class)
        ->sole()
        ->toPendingBatch()
        ->beforeCallbacks()[0]($batchFake);

    $this->assertDatabaseHas(Run::class, [
        'id' => $runId,
        'status' => RunStatus::RUNNING,
        'batch_id' => $batchFake->id,
    ]);
});

test('start collector finishes the run after the batch', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        $runId,
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
    );

    $busFake->assertChained([
        ChainedBatch::class,
        static function (FinishRun $finishRun) use ($runId): bool {
            return $finishRun->runId->equals($runId);
        },
    ]);
});

test('start collector logs the start of the run with its run id in the log context', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $channel = fake()->slug();
    $this->app->make(Repository::class)->set(sprintf('logging.channels.%s', $channel), [
        'driver' => 'monolog',
        'handler' => TestHandler::class,
        'level' => LogLevel::DEBUG,
    ]);
    $this->setConfiguration('compass.log_channel', $channel);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        $runId,
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
    );

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

test('start task records the queued run of the task', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $runId = Uuid::generate();
    $taskType = fake()->randomElement(TaskType::cases());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startTask($runId, $taskType, new CheckSiteEndpoints());

    $this->assertDatabaseHas(Run::class, [
        'id' => $runId,
        'source' => null,
        'collector' => null,
        'task' => $taskType,
        'status' => RunStatus::QUEUED,
    ]);
});

test('start task chains the batch of the first job before finishing the run', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startTask($runId, fake()->randomElement(TaskType::cases()), new CheckSiteEndpoints());

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CheckSiteEndpoints;
        }),
        static function (FinishRun $finishRun) use ($runId): bool {
            return $finishRun->runId->equals($runId);
        },
    ]);
});
