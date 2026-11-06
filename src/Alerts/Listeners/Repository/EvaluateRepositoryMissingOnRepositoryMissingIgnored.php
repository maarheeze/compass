<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Repository;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\RepositoryMissingEvaluator;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;

readonly class EvaluateRepositoryMissingOnRepositoryMissingIgnored implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private RepositoryMissingEvaluator $repositoryMissingEvaluator,
    ) {
    }

    public function handle(RepositoryMissingIgnored $repositoryMissingIgnored): void
    {
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            $this->repositoryMissingEvaluator->conditionObservation($repositoryMissingIgnored->repository),
        ])));
    }
}
