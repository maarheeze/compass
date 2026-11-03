<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateRepositoryMissing implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(RepositoriesCollected|RepositoryMissingIgnored $collectedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        match (true) {
            $collectedEvent instanceof RepositoriesCollected => $this->handleRepositoriesCollected($observedAt),
            $collectedEvent instanceof RepositoryMissingIgnored => $this->handleRepositoryMissingIgnored(
                $collectedEvent,
                $observedAt,
            ),
        };
    }

    private function handleRepositoriesCollected(CarbonImmutable $observedAt): void
    {
        $repositories = Repository::query()->lazyById();

        foreach ($repositories as $repository) {
            $this->observe($repository, $observedAt);
        }
    }

    private function handleRepositoryMissingIgnored(
        RepositoryMissingIgnored $repositoryMissingIgnored,
        CarbonImmutable $observedAt,
    ): void {
        $this->observe($repositoryMissingIgnored->repository, $observedAt);
    }

    private function observe(Repository $repository, CarbonImmutable $observedAt): void
    {
        $this->events->dispatch(new ConditionObserved(
            AlertType::REPOSITORY_MISSING,
            $repository,
            $this->observedCondition($repository),
            $observedAt,
        ));
    }

    private function observedCondition(Repository $repository): ConditionState
    {
        if ($repository->missingState() === MissingState::MISSING) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
