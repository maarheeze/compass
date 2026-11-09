<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Console\ScheduledCommand;
use Maarheeze\Compass\Http\RouteName;

class RunScheduledCommandButton extends Component
{
    public readonly string $runUrl;

    public function __construct(
        public readonly ScheduledCommand $scheduledCommand,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->runUrl = $url->route(RouteName::RUNS_RUN->value);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-scheduled-command-button');
    }
}
