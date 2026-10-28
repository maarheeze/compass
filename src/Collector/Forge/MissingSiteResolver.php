<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Models\Site;

readonly class MissingSiteResolver
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    public function delete(Site $site): void
    {
        Site::query()
            ->whereKey($site->id)
            ->whereNotNull('missing_at')
            ->firstOrFail()
            ->delete();
    }

    public function ignore(Site $site): void
    {
        $missingSite = Site::query()
            ->whereKey($site->id)
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at')
            ->firstOrFail();
        $missingSite->update(['missing_ignored_at' => CarbonImmutable::now()]);

        $this->events->dispatch(new SiteMissingIgnored($missingSite));
    }
}
