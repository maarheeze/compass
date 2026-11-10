<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Maarheeze\Compass\Alerts\Events\AlertChanged;
use Maarheeze\Compass\Alerts\Events\AlertHistoryEntryCreated;
use Maarheeze\Compass\View\ComponentHtml;
use Maarheeze\Compass\View\Components\AlertToast;

readonly class BroadcastAlertHistoryEntry
{
    public function __construct(
        private ?string $broadcastQueue,
        private ComponentHtml $componentHtml,
        private Dispatcher $events,
        private ViewFactory $view,
    ) {
    }

    public function handle(AlertHistoryEntryCreated $alertHistoryEntryCreated): void
    {
        $alertToast = new AlertToast($alertHistoryEntryCreated->alertHistoryEntry, $this->view);

        $this->events->dispatch(new AlertChanged($this->componentHtml->html($alertToast), $this->broadcastQueue));
    }
}
