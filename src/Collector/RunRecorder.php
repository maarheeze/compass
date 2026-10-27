<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Models\Run;

readonly class RunRecorder
{
    public function __construct(
        private CollectorLogger $collectorLogger,
        private Context $context,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function start(RunId $runId, RunType $runType, ShouldQueue $firstJob): void
    {
        $pendingBatch = $this->dispatcher
            ->batch([$firstJob])
            ->allowFailures()
            ->before(static function (Batch $batch) use ($runId, $runType): void {
                Run::query()->create([
                    'id' => $runId,
                    'type' => $runType,
                    'batch_id' => $batch->id,
                ]);
            })
            ->finally(static function (Batch $batch) use ($runId): void {
                Run::query()
                    ->whereKey($runId)
                    ->update([
                        'finished_at' => CarbonImmutable::now(),
                        'failed_job_count' => $batch->failedJobs,
                    ]);
            });

        $collectorLogger = $this->collectorLogger;

        $this->context->scope(
            static function () use ($collectorLogger, $pendingBatch, $runType): void {
                $pendingBatch->dispatch();

                $collectorLogger->info('Run started', ['run_type' => $runType->value]);
            },
            ['run_id' => $runId->toString()],
        );
    }
}
