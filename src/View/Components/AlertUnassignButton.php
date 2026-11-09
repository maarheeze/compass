<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertAssigner;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertUnassignButton extends Component
{
    public readonly bool $canUnassign;
    public readonly string $unassignUrl;

    public function __construct(
        Alert $alert,
        AlertAssigner $alertAssigner,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->canUnassign = $alertAssigner->canUnassign($alert);
        $this->unassignUrl = $urlGenerator->route(RouteName::ALERT_UNASSIGN->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-unassign-button');
    }

    public function shouldRender(): bool
    {
        return $this->canUnassign;
    }
}
