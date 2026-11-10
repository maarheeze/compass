<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Webmozart\Assert\Assert;

class FinishRun implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly Uuid $runId,
    ) {
    }

    public function handle(ClockInterface $clock, QueueingDispatcher $dispatcher): void
    {
        $run = Run::query()
            ->whereKey($this->runId)
            ->sole();
        $batch = Assert::isInstanceOf($dispatcher->findBatch(Assert::string($run->batch_id)), Batch::class);

        $run->update([
            'status' => $batch->failedJobs > 0 ? RunStatus::FAILED : RunStatus::SUCCEEDED,
            'finished_at' => CarbonImmutable::instance($clock->now()),
            'failed_job_count' => $batch->failedJobs,
        ]);
    }
}
