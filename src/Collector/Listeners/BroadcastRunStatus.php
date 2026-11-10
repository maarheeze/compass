<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Maarheeze\Compass\Collector\Events\RunSaved;
use Maarheeze\Compass\Collector\Events\RunStatusChanged;
use Maarheeze\Compass\View\ComponentHtml;
use Maarheeze\Compass\View\Components\RunToast;

readonly class BroadcastRunStatus
{
    public function __construct(
        private ?string $broadcastQueue,
        private ComponentHtml $componentHtml,
        private Dispatcher $events,
        private ViewFactory $view,
    ) {
    }

    public function handle(RunSaved $runSaved): void
    {
        $runToast = new RunToast($runSaved->run, $this->view);

        $this->events->dispatch(new RunStatusChanged($this->componentHtml->html($runToast), $this->broadcastQueue));
    }
}
