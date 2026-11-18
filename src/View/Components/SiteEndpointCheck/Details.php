<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\SiteEndpointCheck;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Models\SiteEndpointCheck;

class Details extends Component
{
    public readonly ?CarbonImmutable $onlineSince;

    public function __construct(
        public readonly SiteEndpointCheck $siteEndpointCheck,
        private readonly ViewFactory $view,
    ) {
        $this->onlineSince = $this->onlineSinceBeforeCheck();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-endpoint-check.details');
    }

    private function onlineSinceBeforeCheck(): ?CarbonImmutable
    {
        $onlineSince = $this->siteEndpointCheck->online_since;

        if ($onlineSince === null) {
            return null;
        }

        return $onlineSince->lt($this->siteEndpointCheck->created_at) ? $onlineSince : null;
    }
}
