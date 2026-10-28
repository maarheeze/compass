<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\Events\RunSucceeded;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class FinishRun implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly Uuid $runId,
        public readonly RunSucceeded $runSucceeded,
    ) {
    }

    public function handle(QueueingDispatcher $dispatcher, Dispatcher $events): void
    {
        $run = Run::query()
            ->whereKey($this->runId)
            ->sole();
        $batch = Assert::isInstanceOf($dispatcher->findBatch($run->batch_id), Batch::class);

        $run->update([
            'status' => $batch->failedJobs > 0 ? RunStatus::FAILED : RunStatus::SUCCEEDED,
            'finished_at' => CarbonImmutable::now(),
            'failed_job_count' => $batch->failedJobs,
        ]);

        if ($run->status !== RunStatus::SUCCEEDED) {
            return;
        }

        $events->dispatch($this->runSucceeded);
    }
}
