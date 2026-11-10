<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Collections\RunCollection;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;

class ToastStack extends Component
{
    public readonly ?AlertHistoryEntryType $confirmedEntryType;
    public readonly RunCollection $runningRuns;

    public function __construct(
        AlertConfirmation $alertConfirmation,
        private readonly ViewFactory $view,
    ) {
        $this->confirmedEntryType = $alertConfirmation->confirmedEntryType();
        $this->runningRuns = Run::query()
            ->where('status', RunStatus::RUNNING)
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.toast-stack');
    }
}
