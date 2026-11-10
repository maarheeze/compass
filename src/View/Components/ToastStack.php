<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;

class ToastStack extends Component
{
    public readonly ?AlertHistoryEntryType $confirmedEntryType;

    public function __construct(
        AlertConfirmation $alertConfirmation,
        private readonly ViewFactory $view,
    ) {
        $this->confirmedEntryType = $alertConfirmation->confirmedEntryType();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.toast-stack');
    }
}
