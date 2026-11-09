<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertRow extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly Alert $alert,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->url = $urlGenerator->route(RouteName::ALERT->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-row');
    }
}
