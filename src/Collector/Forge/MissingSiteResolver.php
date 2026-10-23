<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Models\Site;

readonly class MissingSiteResolver
{
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
        Site::query()
            ->whereKey($site->id)
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at')
            ->firstOrFail()
            ->update(['missing_ignored_at' => CarbonImmutable::now()]);
    }
}
