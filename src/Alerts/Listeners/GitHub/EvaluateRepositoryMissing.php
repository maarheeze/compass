<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateRepositoryMissing implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
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

    private function conditionObserved(Repository $repository, CarbonImmutable $observedAt): ConditionObserved
    {
        return new ConditionObserved(
            AlertType::REPOSITORY_MISSING,
            $repository,
            $this->observedCondition($repository),
            $observedAt,
        );
    }

    private function handleRepositoriesCollected(CarbonImmutable $observedAt): void
    {
        $repositoryChunks = Repository::query()
            ->lazyById()
            ->chunk(100);

        foreach ($repositoryChunks as $repositories) {
            $conditionObservations = new ConditionObservedCollection();

            foreach ($repositories as $repository) {
                $conditionObservations->push($this->conditionObserved($repository, $observedAt));
            }

            $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
        }
    }

    private function handleRepositoryMissingIgnored(
        RepositoryMissingIgnored $repositoryMissingIgnored,
        CarbonImmutable $observedAt,
    ): void {
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservedCollection([
            $this->conditionObserved($repositoryMissingIgnored->repository, $observedAt),
        ])));
    }

    private function observedCondition(Repository $repository): ConditionState
    {
        if ($repository->missingState() === MissingState::MISSING) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
