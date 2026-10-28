<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateMissingRepositories implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(GitHubRunSucceeded|RepositoryMissingIgnored $collectedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        match (true) {
            $collectedEvent instanceof GitHubRunSucceeded => $this->handleGitHubRunSucceeded($observedAt),
            $collectedEvent instanceof RepositoryMissingIgnored => $this->handleRepositoryMissingIgnored(
                $collectedEvent,
                $observedAt,
            ),
        };
    }

    private function handleGitHubRunSucceeded(CarbonImmutable $observedAt): void
    {
        foreach (Repository::query()->get() as $repository) {
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
        $observedCondition = match ($repository->missing_at !== null && $repository->missing_ignored_at === null) {
            true => AlertCondition::PRESENT,
            false => AlertCondition::GONE,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::MISSING_REPOSITORY,
            $repository,
            $observedCondition,
            $observedAt,
        ));
    }
}
