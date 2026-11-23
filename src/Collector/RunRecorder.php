<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Bus\Batch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Collector\Runs\FinishRun;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

readonly class RunRecorder
{
    public const string RUN_ID_CONTEXT_KEY = 'run_id';

    public function __construct(
        private CollectorLogger $collectorLogger,
        private Context $context,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function startCollector(
        Uuid $runId,
        string $source,
        CollectorType $collectorType,
        LockedJob $firstJob,
    ): void {
        Run::query()->create([
            'id' => $runId,
            'source' => $source,
            'collector' => $collectorType,
            'status' => RunStatus::QUEUED,
        ]);

        $this->dispatch($runId, $firstJob, [
            'source' => $source,
            'collector' => $collectorType->value,
        ]);
    }

    public function startSiteInspections(Site $site): void
    {
        foreach ($site->reports as $siteReport) {
            $this->startSiteInspection($site, $siteReport->source);
        }
    }

    public function startTask(Uuid $runId, TaskType $taskType, LockedJob $firstJob): void
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
    private function dispatch(Uuid $runId, LockedJob $firstJob, array $runContext): void
    {
        $pendingBatch = $this->dispatcher
            ->batch([$firstJob])
            ->allowFailures()
            ->before(static function (Batch $batch) use ($runId): void {
                Run::query()
                    ->whereKey($runId)
                    ->sole()
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
            [self::RUN_ID_CONTEXT_KEY => $runId->toString()],
        );
    }

    private function startSiteInspection(Site $site, string $source): void
    {
        $runId = Uuid::generate();
        $run = new Run([
            'id' => $runId,
            'source' => $source,
            'collector' => CollectorType::SITE_REPORTS,
            'status' => RunStatus::QUEUED,
        ]);
        $run->subject()->associate($site);
        $run->save();

        $this->dispatch($runId, new InspectSite($site, $source), [
            'source' => $source,
            'collector' => CollectorType::SITE_REPORTS->value,
            'site' => $site->name,
        ]);
    }
}
