<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Models\Repository;

readonly class MissingRepositoryResolver
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

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
        $missingRepository = Repository::query()
            ->whereKey($repository->id)
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at')
            ->firstOrFail();
        $missingRepository->update(['missing_ignored_at' => CarbonImmutable::now()]);

        $this->events->dispatch(new RepositoryMissingIgnored($missingRepository));
    }
}
