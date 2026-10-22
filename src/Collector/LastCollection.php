<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Support\Carbon;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

readonly class LastCollection
{
    public function collectedAt(): ?Carbon
    {
        $repositoriesCollectedAt = Repository::query()
            ->latest('collected_at')
            ->first()
            ?->collected_at;
        $sitesCollectedAt = Site::query()
            ->latest('collected_at')
            ->first()
            ?->collected_at;

        return match (true) {
            $repositoriesCollectedAt === null => $sitesCollectedAt,
            $sitesCollectedAt === null => $repositoriesCollectedAt,
            default => $repositoriesCollectedAt->min($sitesCollectedAt),
        };
    }
}
