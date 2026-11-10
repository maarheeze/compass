<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Models\Builders\SiteBuilder;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Throwable;

readonly class MissingSiteResolver
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
        private Dispatcher $events,
    ) {
    }

    public function canIgnore(Site $site): bool
    {
        return $this->ignorableSiteQuery($site)->exists();
    }

    /**
     * @throws Throwable
     */
    public function delete(Site $site): void
    {
        $missingSite = Site::query()
            ->whereKey($site->id)
            ->notCollected()
            ->with('reports.endpoints')
            ->firstOrFail();

        $this->connection->transaction(static function () use ($missingSite): void {
            foreach ($missingSite->reports as $siteReport) {
                foreach ($siteReport->endpoints as $siteEndpoint) {
                    $siteEndpoint->delete();
                }

                $siteReport->delete();
            }

            $missingSite->delete();
        });
    }

    public function ignore(Site $site): void
    {
        $missingSite = $this->ignorableSiteQuery($site)->firstOrFail();
        $missingSite->update(['missing_ignored_at' => CarbonImmutable::instance($this->clock->now())]);

        $this->events->dispatch(new SiteMissingIgnored($missingSite));
    }

    private function ignorableSiteQuery(Site $site): SiteBuilder
    {
        return Site::query()
            ->whereKey($site->id)
            ->missing();
    }
}
