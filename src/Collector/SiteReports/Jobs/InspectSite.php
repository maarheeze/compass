<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollector;
use Maarheeze\Compass\Collector\SiteReports\SiteReportSynchroniser;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Throwable;

use function sprintf;

class InspectSite implements SubjectJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;
    use SerializesModels;

    public function __construct(
        public readonly Site $site,
        public readonly string $source,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws Throwable
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        Dispatcher $events,
        SiteReportSynchroniser $siteReportSynchroniser,
        SourceDrivers $sourceDrivers,
    ): void {
        $siteReportsCollector = $sourceDrivers->collector($this->source, SiteReportsCollector::class);

        $context->add('site', $this->site->name);

        if ($this->skipsSubject()) {
            $collectorLogger->debug('Site inspection skipped for a missing site');

            return;
        }

        $siteReport = SiteReport::query()->firstOrNew(['site_id' => $this->site->id, 'source' => $this->source]);
        $siteReportSynchroniser->storeReport($siteReport, $siteReportsCollector->siteReport($siteReport));

        $collectorLogger->debug('Site inspected', ['status' => $siteReport->status->value]);

        $events->dispatch(new SiteInspected($siteReport));
    }

    public function lockKey(): string
    {
        return sprintf('%s:%s', $this->site->id->toString(), $this->source);
    }

    public function skipsSubject(): bool
    {
        return $this->site->missingState() !== MissingState::COLLECTED;
    }

    public function subject(): AlertSubject&Model
    {
        return $this->site;
    }
}
