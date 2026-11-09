<?php

declare(strict_types=1);

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Issues\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
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
