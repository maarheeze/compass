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

    public function start(
        Uuid $runId,
        Source $source,
        CollectionType $collectionType,
        ShouldQueue $firstJob,
        RunSucceeded $runSucceeded,
    ): void {
        Run::query()->create([
            'id' => $runId,
            'source' => $source->key,
            'collection' => $collectionType,
            'status' => RunStatus::QUEUED,
        ]);

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
            $this->dispatcher->chain([$pendingBatch, new FinishRun($runId, $runSucceeded)]),
            PendingChain::class,
        );

        $collectorLogger = $this->collectorLogger;

        $this->context->scope(
            static function () use ($collectionType, $collectorLogger, $pendingChain, $source): void {
                $pendingChain->dispatch();

                $collectorLogger->info('Run started', [
                    'source' => $source->key,
                    'collection' => $collectionType->value,
                ]);
            },
            ['run_id' => $runId->toString()],
        );
    }
}
