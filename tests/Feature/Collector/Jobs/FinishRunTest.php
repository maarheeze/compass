<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\BatchRepositoryFake;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;

test('handle marks a run without failed jobs succeeded', function (): void {
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id, new GitHubRunSucceeded($run->id));
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

    $finishRun = new FinishRun($run->id, new GitHubRunSucceeded($run->id));
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

    $finishRun = new FinishRun($run->id, new GitHubRunSucceeded($run->id));
    $this->app->call([$finishRun, 'handle']);

    $this->assertDatabaseHas(Run::class, [
        'id' => $run->id,
        'failed_job_count' => $batch->failedJobs,
    ]);
});

test('handle records when the run finished', function (): void {
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id, new GitHubRunSucceeded($run->id));
    $this->app->call([$finishRun, 'handle']);

    expect($run->refresh()->finished_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('handle raises the succeeded event of a succeeded run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);
    $gitHubRunSucceeded = new GitHubRunSucceeded($run->id);

    $finishRun = new FinishRun($run->id, $gitHubRunSucceeded);
    $this->app->call([$finishRun, 'handle']);

    $eventFake->assertDispatched(GitHubRunSucceeded::class, static function (GitHubRunSucceeded $dispatchedGitHubRunSucceeded) use ($gitHubRunSucceeded): bool {
        return $dispatchedGitHubRunSucceeded === $gitHubRunSucceeded;
    });
});

test('handle raises nothing for a failed run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $batchRepositoryFake = new BatchRepositoryFake();
    $batch = $batchRepositoryFake->store($this->app->make(QueueingDispatcher::class)->batch([]));
    $batch->failedJobs = fake()->numberBetween(1, 10);
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class), [], $batchRepositoryFake));
    $run = Run::factory()->create(['batch_id' => $batch->id]);

    $finishRun = new FinishRun($run->id, new GitHubRunSucceeded($run->id));
    $this->app->call([$finishRun, 'handle']);

    $eventFake->assertNothingDispatched();
});
