<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\SiteEndpointCheck;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\View\BadgeType;

class StatusLabel extends Component
{
    public function __construct(
        public readonly SiteEndpointCheck $siteEndpointCheck,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return match ($this->siteEndpointCheck->state) {
            SiteEndpointState::DOWN => BadgeType::DANGER,
            SiteEndpointState::UP => BadgeType::SUCCESS,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-endpoint-check.status-label');
    }
}
