<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertCheckStateClassifier;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Collections\AlertCheckStateCountCollection;

class AlertCheckTable extends Component
{
    public readonly AlertCheckStateCountCollection $alertCheckStateCounts;
    public readonly AlertCheckCollection $failingAlertChecks;
    public readonly AlertCheckCollection $notFailingAlertChecks;

    public function __construct(
        AlertCheckCollection $alertChecks,
        AlertCheckStateClassifier $alertCheckStateClassifier,
        private readonly ViewFactory $view,
    ) {
        $this->alertCheckStateCounts = $alertChecks->stateCounts($alertCheckStateClassifier);
        $this->failingAlertChecks = $alertChecks->failing($alertCheckStateClassifier);
        $this->notFailingAlertChecks = $alertChecks->notFailing($alertCheckStateClassifier);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-check-table');
    }
}
