<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ComposerWarningEvaluator;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;

readonly class EvaluateComposerWarningOnComposerJsonChanged implements ShouldQueue
{
    public function __construct(
        private ComposerWarningEvaluator $composerWarningEvaluator,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(ComposerJsonChanged $composerJsonChanged): void
    {
        $this->dispatcher->dispatch(new HandleConditionObservations(
            $this->composerWarningEvaluator->conditionObservations($composerJsonChanged->repository),
        ));
    }
}
