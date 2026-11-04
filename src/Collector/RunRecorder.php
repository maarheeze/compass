<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Bus\Batch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

readonly class RunRecorder
{
    public function __construct(
        private CollectorLogger $collectorLogger,
        private Context $context,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function startCollector(
        Uuid $runId,
        string $sourceKey,
        CollectorType $collectorType,
        ShouldQueue $firstJob,
    ): void {
        Run::query()->create([
            'id' => $runId,
            'source' => $sourceKey,
            'collector' => $collectorType,
            'status' => RunStatus::QUEUED,
        ]);

        $this->dispatch($runId, $firstJob, [
            'source' => $sourceKey,
            'collector' => $collectorType->value,
        ]);
    }

    public function startTask(Uuid $runId, TaskType $taskType, ShouldQueue $firstJob): void
    {
        Run::query()->create([
            'id' => $runId,
            'task' => $taskType,
            'status' => RunStatus::QUEUED,
        ]);

        $this->dispatch($runId, $firstJob, ['task' => $taskType->value]);
    }

    /**
     * @param array<string, string> $runContext
     */
    private function dispatch(Uuid $runId, ShouldQueue $firstJob, array $runContext): void
    {
        $pendingBatch = $this->dispatcher
            ->batch([$firstJob])
            ->allowFailures()
            ->before(static function (Batch $batch) use ($runId): void {
                Run::query()
                    ->whereKey($runId)
                    ->update([
                        'status' => RunStatus::RUNNING,
                        'batch_id' => $batch->id,
                    ]);
            });
        $pendingChain = Assert::isInstanceOf(
            $this->dispatcher->chain([$pendingBatch, new FinishRun($runId)]),
            PendingChain::class,
        );

        $collectorLogger = $this->collectorLogger;

        $this->context->scope(
            static function () use ($collectorLogger, $pendingChain, $runContext): void {
                $pendingChain->dispatch();

                $collectorLogger->info('Run started', $runContext);
            },
            ['run_id' => $runId->toString()],
        );
    }
}
