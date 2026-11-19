<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\AlertLevelCountCollection;

class AlertTable extends Component
{
    public readonly AlertLevelCountCollection $alertLevelCounts;
    public readonly int $doneAlertCount;
    public readonly int $mutedOpenAlertCount;
    public readonly AlertCollection $mutedOrDoneAlerts;
    public readonly AlertCollection $unmutedOpenAlerts;

    public function __construct(
        AlertCollection $alerts,
        AlertTypes $alertTypes,
        private readonly ViewFactory $view,
    ) {
        $this->unmutedOpenAlerts = $alerts->unmutedOpen();
        $this->alertLevelCounts = $this->unmutedOpenAlerts->levelCounts($alertTypes);
        $this->mutedOrDoneAlerts = $alerts->mutedOrDone();
        $this->mutedOpenAlertCount = $alerts->mutedOpen()->count();
        $this->doneAlertCount = $alerts->done()->count();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-table');
    }
}
