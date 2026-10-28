<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\ConditionPresent;

readonly class AlertRecorder implements ShouldQueue
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    public function handle(ConditionPresent $conditionPresent): void
    {
        $alert = $conditionPresent->subject
            ->alerts()
            ->create([
                'type' => $conditionPresent->alertType,
                'condition' => AlertCondition::PRESENT,
                'last_observed_at' => $conditionPresent->observedAt,
                'status' => AlertStatus::OPEN,
            ]);

        $this->events->dispatch(new AlertOpened($alert));
    }
}
