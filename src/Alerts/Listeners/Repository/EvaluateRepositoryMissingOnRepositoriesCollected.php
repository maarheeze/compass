<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Repository;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\RepositoryMissingEvaluator;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Models\Repository;

readonly class EvaluateRepositoryMissingOnRepositoriesCollected implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private RepositoryMissingEvaluator $repositoryMissingEvaluator,
    ) {
    }

    public function handle(RepositoriesCollected $repositoriesCollected): void
    {
        $repositoryChunks = Repository::query()
            ->ofSource($repositoriesCollected->source)
            ->lazyById()
            ->chunk(100);

        foreach ($repositoryChunks as $repositories) {
            $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection(
                $repositories->map($this->repositoryMissingEvaluator->conditionObservation(...)),
            )));
        }
    }
}
