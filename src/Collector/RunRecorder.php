<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Bus\Batch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Collector\Events\RunSucceeded;
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

    public function start(Uuid $runId, RunType $runType, ShouldQueue $firstJob, RunSucceeded $runSucceeded): void
    {
        $pendingBatch = $this->dispatcher
            ->batch([$firstJob])
            ->allowFailures()
            ->before(static function (Batch $batch) use ($runId, $runType): void {
                Run::query()->create([
                    'id' => $runId,
                    'type' => $runType,
                    'status' => RunStatus::RUNNING,
                    'batch_id' => $batch->id,
                ]);
            });
        $pendingChain = Assert::isInstanceOf(
            $this->dispatcher->chain([$pendingBatch, new FinishRun($runId, $runSucceeded)]),
            PendingChain::class,
        );

        $collectorLogger = $this->collectorLogger;

        $this->context->scope(
            static function () use ($collectorLogger, $pendingChain, $runType): void {
                $pendingChain->dispatch();

                $collectorLogger->info('Run started', ['run_type' => $runType->value]);
            },
            ['run_id' => $runId->toString()],
        );
    }
}
