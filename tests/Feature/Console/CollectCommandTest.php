<?php

declare(strict_types=1);

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectorRegistry;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Console\CollectCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run of the collector of the source', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $collectorType = fake()->randomElement(CollectorType::cases());

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => 'github',
        'collector' => $collectorType->value,
    ]);
    $pendingCommand->assertSuccessful();

    $this->assertDatabaseHas(Run::class, [
        'source' => 'github',
        'collector' => $collectorType,
    ]);
});

test('handle runs the job of the collector from the registry', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $collectorType = fake()->randomElement(CollectorType::cases());
    $this->app
        ->make(CollectorRegistry::class)
        ->register($collectorType, CollectIssues::class);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => 'github',
        'collector' => $collectorType->value,
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
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $this->expectException(ValueError::class);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => 'github',
        'collector' => fake()->slug(),
    ]);
    $pendingCommand->run();
});

test('handle refuses a source that is not configured', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $this->expectException(InvalidArgumentException::class);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collector' => fake()->randomElement(CollectorType::cases())->value,
    ]);
    $pendingCommand->run();
});
