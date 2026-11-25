<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains\Jobs;

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
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainsCollector;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainSynchroniser;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Site;
use Throwable;

class CollectSiteDomains implements SubjectJob
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
     * @throws Throwable
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        SiteDomainSynchroniser $siteDomainSynchroniser,
        SourceDrivers $sourceDrivers,
    ): void {
        $siteDomainsCollector = $sourceDrivers->collector($this->site->source, SiteDomainsCollector::class);

        $context->add('site', $this->site->name);

        $collectedSiteDomains = $siteDomainsCollector->siteDomains($this->site);
        $siteDomainSynchroniser->storeSiteDomains($this->site, $collectedSiteDomains);

        $collectorLogger->debug('Site domains collected', ['domain_count' => $collectedSiteDomains->count()]);
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
