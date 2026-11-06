<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ComposerWarningEvaluator;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;

readonly class EvaluateComposerWarningOnComposerLockChanged implements ShouldQueue
{
    public function __construct(
        private ComposerWarningEvaluator $composerWarningEvaluator,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(ComposerLockChanged $composerLockChanged): void
    {
        $this->dispatcher->dispatch(new HandleConditionObservations(
            $this->composerWarningEvaluator->conditionObservations($composerLockChanged->repository),
        ));
    }
}
