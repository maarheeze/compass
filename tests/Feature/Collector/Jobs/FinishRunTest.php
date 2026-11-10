<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BatchRepositoryFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle marks a run without failed jobs succeeded', function (): void {
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id);
    $this->app->call([$finishRun, 'handle']);

    $this->assertDatabaseHas(Run::class, [
        'id' => $run->id,
        'status' => RunStatus::SUCCEEDED,
    ]);
});

test('handle marks a run with failed jobs failed', function (): void {
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $batch->failedJobs = fake()->numberBetween(1, 10);
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id);
    $this->app->call([$finishRun, 'handle']);

    $this->assertDatabaseHas(Run::class, [
        'id' => $run->id,
        'status' => RunStatus::FAILED,
    ]);
});

test('handle records the failed job count of the run', function (): void {
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $batch->failedJobs = fake()->numberBetween(0, 10);
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id);
    $this->app->call([$finishRun, 'handle']);

    $this->assertDatabaseHas(Run::class, [
        'id' => $run->id,
        'failed_job_count' => $batch->failedJobs,
    ]);
});

test('handle records when the run finished', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id);
    $this->app->call([$finishRun, 'handle']);

    $this->assertDatabaseHas(Run::class, [
        'id' => $run->id,
        'finished_at' => $now,
    ]);
});
