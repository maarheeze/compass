<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Models\Builders\AlertBuilder;

class AlertIcon extends Component
{
    public readonly bool $hasAlertsNotMuted;
    private readonly bool $hasAlertsNotDone;

    public function __construct(
        AlertBuilder $alertQuery,
        private readonly ViewFactory $view,
    ) {
        $this->hasAlertsNotDone = $alertQuery
            ->clone()
            ->notDone()
            ->exists();
        $this->hasAlertsNotMuted = $alertQuery
            ->clone()
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
