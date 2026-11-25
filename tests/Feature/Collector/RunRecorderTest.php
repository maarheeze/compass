<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Bus\ChainedBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Testing\Fakes\BatchFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Events\RunStatusChanged;
use Maarheeze\Compass\Collector\QueuePriority;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\Runs\FinishRun;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
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
        fake()->randomElement(QueuePriority::cases()),
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
        fake()->randomElement(QueuePriority::cases()),
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
    $source = fake()->slug();
    $collectorType = fake()->randomElement(CollectorType::cases());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        $runId,
        $source,
        $collectorType,
        new CheckSiteEndpoints(),
        fake()->randomElement(QueuePriority::cases()),
    );

    $this->assertDatabaseHas(Run::class, [
        'id' => $runId,
        'source' => $source,
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
        fake()->randomElement(QueuePriority::cases()),
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

test('start collector broadcasts the running run when the batch is stored', function (): void {
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
        fake()->randomElement(QueuePriority::cases()),
    );
    $eventFake = new EventFake($this->app->make(Dispatcher::class), [RunStatusChanged::class]);
    $this->app->instance(Dispatcher::class, $eventFake);
    $busFake
        ->dispatched(ChainedBatch::class)
        ->sole()
        ->toPendingBatch()
        ->beforeCallbacks()[0]($batchFake);

    $eventFake->assertDispatched(RunStatusChanged::class, static function (RunStatusChanged $runStatusChanged) use ($runId): bool {
        return $runStatusChanged->toastHtml !== null
            && str_contains($runStatusChanged->toastHtml, sprintf('data-compass-toast-id="%s"', $runId))
            && str_contains($runStatusChanged->toastHtml, 'data-compass-toast-stays-open');
    });
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
        fake()->randomElement(QueuePriority::cases()),
    );

    $busFake->assertChained([
        ChainedBatch::class,
        static function (FinishRun $finishRun) use ($runId): bool {
            return $finishRun->runId->equals($runId);
        },
    ]);
});

test('start collector puts the batch on the queue of the priority', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $highQueue = fake()->slug();
    $this->setConfiguration('compass.high_queue', $highQueue);

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        Uuid::generate(),
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
        QueuePriority::HIGH,
    );

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($highQueue): bool {
            return $pendingBatch->queue() === $highQueue;
        }),
        FinishRun::class,
    ]);
});

test('start collector finishes the run on the queue of the priority', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $highQueue = fake()->slug();
    $this->setConfiguration('compass.high_queue', $highQueue);

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startCollector(
        Uuid::generate(),
        fake()->slug(),
        fake()->randomElement(CollectorType::cases()),
        new CheckSiteEndpoints(),
        QueuePriority::HIGH,
    );

    $chainQueue = $busFake
        ->dispatched(ChainedBatch::class)
        ->sole()
        ->chainQueue;

    expect($chainQueue)
        ->toBe($highQueue);
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
        fake()->randomElement(QueuePriority::cases()),
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

test('start site inspections records a queued site reports run with the site as its subject for each source of its reports', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $site = Site::factory()->create();
    $siteReports = SiteReport::factory()
        ->count(2)
        ->create(['site_id' => $site->id]);

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startSiteInspections($site);

    $inspectedSources = Run::query()
        ->where('subject_type', $site->getMorphClass())
        ->where('subject_id', $site->id)
        ->where('collector', CollectorType::SITE_REPORTS)
        ->where('status', RunStatus::QUEUED)
        ->pluck('source');

    expect($inspectedSources->all())
        ->toEqualCanonicalizing($siteReports->pluck('source')->all());
});

test('start site inspections chains the batch of the inspection of the site for the source of its report before finishing the run', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create();
    $siteReport = SiteReport::factory()->create(['site_id' => $site->id]);

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startSiteInspections($site);

    $runId = Run::query()->sole()->id;
    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($site, $siteReport): bool {
            $inspectSite = $pendingBatch->jobs->sole();

            return $inspectSite instanceof InspectSite
                && $inspectSite->site->id->equals($site->id)
                && $inspectSite->source === $siteReport->source;
        }),
        static function (FinishRun $finishRun) use ($runId): bool {
            return $finishRun->runId->equals($runId);
        },
    ]);
});

test('start site inspections puts the batch on the high queue', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $highQueue = fake()->slug();
    $this->setConfiguration('compass.high_queue', $highQueue);
    $site = Site::factory()->create();
    SiteReport::factory()->create(['site_id' => $site->id]);

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startSiteInspections($site);

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($highQueue): bool {
            return $pendingBatch->queue() === $highQueue;
        }),
        FinishRun::class,
    ]);
});

test('start task puts the batch on the default queue', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $this->setConfiguration('compass.high_queue', fake()->unique()->slug());
    $this->setConfiguration('compass.low_queue', fake()->unique()->slug());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->startTask(Uuid::generate(), fake()->randomElement(TaskType::cases()), new CheckSiteEndpoints());

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->queue() === null;
        }),
        FinishRun::class,
    ]);
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
