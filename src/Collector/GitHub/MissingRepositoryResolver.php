<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Models\Repository;

readonly class MissingRepositoryResolver
{
    public function delete(Repository $repository): void
    {
        Repository::query()
            ->whereKey($repository->id)
            ->whereNotNull('missing_at')
            ->firstOrFail()
            ->delete();
    }

    public function ignore(Repository $repository): void
    {
        Repository::query()
            ->whereKey($repository->id)
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at')
            ->firstOrFail()
            ->update(['missing_ignored_at' => CarbonImmutable::now()]);
    }
}
