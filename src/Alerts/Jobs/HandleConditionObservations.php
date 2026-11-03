<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Throwable;

#[Tries(5)]
class HandleConditionObservations implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly ConditionObservedCollection $conditionObservations,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(ObservationHandler $observationHandler): void
    {
        foreach ($this->conditionObservations as $conditionObserved) {
            $observationHandler->handle($conditionObserved);
        }
    }
}
