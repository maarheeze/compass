<?php

declare(strict_types=1);

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Issues\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\QueuePriority;
use Maarheeze\Compass\Collector\Runs\FinishRun;
use Maarheeze\Compass\Console\CollectCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run of the collector of the source', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $source = fake()->slug();
    $collectorType = fake()->randomElement(CollectorType::cases());

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => $source,
        'collector' => $collectorType->value,
    ]);
    $pendingCommand->run();

    $this->assertDatabaseHas(Run::class, [
        'source' => $source,
        'collector' => $collectorType,
    ]);
});

test('handle runs the job of the collector type', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => CollectorType::ISSUES->value,
    ]);
    $pendingCommand->run();

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CollectIssues;
        }),
        FinishRun::class,
    ]);
});

test('handle puts the run on the queue of the given priority', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $highQueue = fake()->slug();
    $this->setConfiguration('compass.high_queue', $highQueue);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => fake()->randomElement(CollectorType::cases())->value,
        '--priority' => QueuePriority::HIGH->value,
    ]);
    $pendingCommand->run();

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($highQueue): bool {
            return $pendingBatch->queue() === $highQueue;
        }),
        FinishRun::class,
    ]);
});

test('handle puts the run on the queue of the scheduled priority of the collector without a priority', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $lowQueue = fake()->unique()->slug();
    $this->setConfiguration('compass.high_queue', fake()->unique()->slug());
    $this->setConfiguration('compass.low_queue', $lowQueue);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => CollectorType::REPOSITORIES->value,
    ]);
    $pendingCommand->run();

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($lowQueue): bool {
            return $pendingBatch->queue() === $lowQueue;
        }),
        FinishRun::class,
    ]);
});

test('handle refuses a priority that does not exist', function (): void {
    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => fake()->randomElement(CollectorType::cases())->value,
        '--priority' => fake()->slug(),
    ]);

    $pendingCommand->assertFailed();
});

test('handle starts no run for a priority that does not exist', function (): void {
    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => fake()->randomElement(CollectorType::cases())->value,
        '--priority' => fake()->slug(),
    ]);
    $pendingCommand->run();

    $this->assertDatabaseEmpty(Run::class);
});

test('handle refuses a collector that does not exist', function (): void {
    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => fake()->slug(),
    ]);

    $pendingCommand->assertFailed();
});

test('handle starts no run for a collector that does not exist', function (): void {
    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => fake()->slug(),
    ]);
    $pendingCommand->run();

    $this->assertDatabaseEmpty(Run::class);
});
