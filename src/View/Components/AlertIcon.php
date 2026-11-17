<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\AlertLevelCountCollection;

class AlertIcon extends Component
{
    public readonly AlertLevelCountCollection $alertLevelCounts;
    private readonly bool $hasAlertsNotDone;

    public function __construct(
        AlertSubject $alertSubject,
        AlertTypes $alertTypes,
        private readonly ViewFactory $view,
    ) {
        $this->alertLevelCounts = $alertSubject
            ->alertsIncludingChildren()
            ->notDone()
            ->notMuted()
            ->get()
            ->levelCounts($alertTypes);
        $this->hasAlertsNotDone = $alertSubject
            ->alertsIncludingChildren()
            ->notDone()
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
