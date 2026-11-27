<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteProcesses\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SiteProcesses\SiteProcessesCollector;
use Maarheeze\Compass\Collector\SiteProcesses\SiteProcessesSynchroniser;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Site;

class CollectSiteProcesses implements SubjectJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;
    use SerializesModels;

    public function __construct(
        public readonly Site $site,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        SiteProcessesSynchroniser $siteProcessesSynchroniser,
        SourceDrivers $sourceDrivers,
    ): void {
        $siteProcessesCollector = $sourceDrivers->collector($this->site->source, SiteProcessesCollector::class);

        $context->add('site', $this->site->name);

        $collectedSiteProcesses = $siteProcessesCollector->siteProcesses($this->site);
        $siteProcessesSynchroniser->storeSiteProcesses($this->site, $collectedSiteProcesses);

        $collectorLogger->debug('Site processes collected', [
            'queue_worker_state' => $collectedSiteProcesses->queueWorkerState->value,
        ]);
    }

    public function lockKey(): string
    {
        return $this->site->id->toString();
    }

    public function skipsSubject(): bool
    {
        return false;
    }

    public function subject(): AlertSubject&Model
    {
        return $this->site;
    }
}
