<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Models\Site;
use Throwable;

readonly class MissingSiteResolver
{
    public function __construct(
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
            ->whereNotNull('missing_at')
            ->with('endpoints')
            ->firstOrFail();

        $this->connection->transaction(static function () use ($missingSite): void {
            foreach ($missingSite->endpoints as $siteEndpoint) {
                $siteEndpoint->delete();
            }

            $missingSite->delete();
        });
    }

    public function ignore(Site $site): void
    {
        $missingSite = $this->ignorableSiteQuery($site)->firstOrFail();
        $missingSite->update(['missing_ignored_at' => CarbonImmutable::now()]);

        $this->events->dispatch(new SiteMissingIgnored($missingSite));
    }

    /**
     * @return Builder<Site>
     */
    private function ignorableSiteQuery(Site $site): Builder
    {
        return Site::query()
            ->whereKey($site->id)
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at');
    }
}
