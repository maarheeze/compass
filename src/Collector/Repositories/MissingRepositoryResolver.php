<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Models\Builders\RepositoryBuilder;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Throwable;

readonly class MissingRepositoryResolver
{
    public function __construct(
        private ClockInterface $clock,
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
            ->notCollected()
            ->firstOrFail();

        $this->connection->transaction(static function () use ($missingRepository): void {
            $missingRepository->delete();
        });
    }

    public function ignore(Repository $repository): void
    {
        $missingRepository = $this->ignorableRepositoryQuery($repository)->firstOrFail();
        $missingRepository->update(['missing_ignored_at' => CarbonImmutable::instance($this->clock->now())]);

        $this->events->dispatch(new RepositoryMissingIgnored($missingRepository));
    }

    private function ignorableRepositoryQuery(Repository $repository): RepositoryBuilder
    {
        return Repository::query()
            ->whereKey($repository->id)
            ->missing();
    }
}
