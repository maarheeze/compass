<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Models\Repository;
use Throwable;

readonly class MissingRepositoryResolver
{
    public function __construct(
        private ConnectionInterface $connection,
        private Dispatcher $events,
    ) {
    }

    public function canIgnore(Repository $repository): bool
    {
        return $this->ignorableRepositoryQuery($repository)->exists();
    }

    /**
     * @throws Throwable
     */
    public function delete(Repository $repository): void
    {
        $missingRepository = Repository::query()
            ->whereKey($repository->id)
            ->whereNotNull('missing_at')
            ->firstOrFail();

        $this->connection->transaction(static function () use ($missingRepository): void {
            $missingRepository->delete();
        });
    }

    public function ignore(Repository $repository): void
    {
        $missingRepository = $this->ignorableRepositoryQuery($repository)->firstOrFail();
        $missingRepository->update(['missing_ignored_at' => CarbonImmutable::now()]);

        $this->events->dispatch(new RepositoryMissingIgnored($missingRepository));
    }

    /**
     * @return Builder<Repository>
     */
    private function ignorableRepositoryQuery(Repository $repository): Builder
    {
        return Repository::query()
            ->whereKey($repository->id)
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at');
    }
}
