<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertSubject;

class AlertIcon extends Component
{
    public readonly bool $hasAlertsNotMuted;
    private readonly bool $hasAlertsNotDone;

    public function __construct(
        AlertSubject $subject,
        private readonly ViewFactory $view,
    ) {
        $this->hasAlertsNotDone = $subject
            ->alertsIncludingChildren()
            ->notDone()
            ->exists();
        $this->hasAlertsNotMuted = $subject
            ->alertsIncludingChildren()
            ->notDone()
            ->notMuted()
            ->exists();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-icon');
    }

    public function shouldRender(): bool
    {
        return $this->hasAlertsNotDone;
    }
}
