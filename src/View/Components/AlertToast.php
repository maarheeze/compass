<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\View\ToastType;

use function in_array;

class AlertToast extends Component
{
    public function __construct(
        public readonly AlertHistoryEntry $alertHistoryEntry,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-toast');
    }

    public function shouldRender(): bool
    {
        return in_array($this->alertHistoryEntry->type, [
            AlertHistoryEntryType::OPENED,
            AlertHistoryEntryType::RECURRED,
            AlertHistoryEntryType::RESOLVED,
        ], true);
    }

    public function toastType(): ToastType
    {
        return match ($this->alertHistoryEntry->type) {
            AlertHistoryEntryType::OPENED, AlertHistoryEntryType::RECURRED => ToastType::DANGER,
            AlertHistoryEntryType::RESOLVED => ToastType::SUCCESS,
            AlertHistoryEntryType::ASSIGNED,
            AlertHistoryEntryType::MARKED_DONE,
            AlertHistoryEntryType::MUTED,
            AlertHistoryEntryType::REOPENED,
            AlertHistoryEntryType::UNASSIGNED,
            AlertHistoryEntryType::UNMUTED => ToastType::NEUTRAL,
        };
    }
}
