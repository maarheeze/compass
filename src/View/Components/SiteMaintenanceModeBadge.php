<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Models\Site;

class SiteMaintenanceModeBadge extends Component
{
    private readonly bool $hasMaintenanceModeOn;

    public function __construct(
        Site $site,
        private readonly ViewFactory $view,
    ) {
        $this->hasMaintenanceModeOn = $site->reports->hasMaintenanceModeOn();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-maintenance-mode-badge');
    }

    public function shouldRender(): bool
    {
        return $this->hasMaintenanceModeOn;
    }
}
