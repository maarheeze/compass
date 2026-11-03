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
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\CollectionType;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\GitHub\GitHubDriver;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\Source;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Uuid\Uuid;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

test('start chains the batch with the first job', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(
        $runId,
        new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1())),
        fake()->randomElement(CollectionType::cases()),
        new InspectSites(),
        new GitHubRunSucceeded($runId),
    );

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof InspectSites;
        }),
        FinishRun::class,
    ]);
});

test('start keeps the batch going when one of its jobs fails', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(
        $runId,
        new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1())),
        fake()->randomElement(CollectionType::cases()),
        new InspectSites(),
        new GitHubRunSucceeded($runId),
    );

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->allowsFailures();
        }),
        FinishRun::class,
    ]);
});

test('start records the queued run of the collection of the source', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $runId = Uuid::generate();
    $source = new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1()));
    $collectionType = fake()->randomElement(CollectionType::cases());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start($runId, $source, $collectionType, new InspectSites(), new GitHubRunSucceeded($runId));

    $this->assertDatabaseHas(Run::class, [
        'id' => $runId,
        'source' => $source->key,
        'collection' => $collectionType,
        'status' => RunStatus::QUEUED,
        'batch_id' => null,
    ]);
});

test('start records the run as running with its batch when the batch is stored', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();
    $batchFake = new BatchFake(fake()->uuid(), '', 1, 1, 0, [], [], CarbonImmutable::now());

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(
        $runId,
        new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1())),
        fake()->randomElement(CollectionType::cases()),
        new InspectSites(),
        new GitHubRunSucceeded($runId),
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

test('start finishes the run with its succeeded event after the batch', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(
        $runId,
        new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1())),
        fake()->randomElement(CollectionType::cases()),
        new InspectSites(),
        new GitHubRunSucceeded($runId),
    );

    $busFake->assertChained([
        ChainedBatch::class,
        static function (FinishRun $finishRun) use ($runId): bool {
            return $finishRun->runId->equals($runId)
                && $finishRun->runSucceeded instanceof GitHubRunSucceeded
                && $finishRun->runSucceeded->runId->equals($runId);
        },
    ]);
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
    $runId = Uuid::generate();

    $runRecorder = $this->app->make(RunRecorder::class);
    $runRecorder->start(
        $runId,
        new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1())),
        fake()->randomElement(CollectionType::cases()),
        new InspectSites(),
        new GitHubRunSucceeded($runId),
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
