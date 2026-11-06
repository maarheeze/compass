<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\GitHub;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\DependabotCoverageEvaluator;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;

readonly class EvaluateDependabotCoverageOnPackageJsonChanged implements ShouldQueue
{
    public function __construct(
        private DependabotCoverageEvaluator $dependabotCoverageEvaluator,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(PackageJsonChanged $packageJsonChanged): void
    {
        $repository = $packageJsonChanged->repository->load(['composerJson', 'dependabotYml']);

        $this->dispatcher->dispatch(new HandleConditionObservations(
            $this->dependabotCoverageEvaluator->conditionObservations($repository),
        ));
    }
}
