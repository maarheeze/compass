<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertReopener;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertReopenButton extends Component
{
    public readonly bool $canReopen;
    public readonly string $reopenUrl;

    public function __construct(
        Alert $alert,
        AlertReopener $alertReopener,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->canReopen = $alertReopener->canReopen($alert);
        $this->reopenUrl = $urlGenerator->route(RouteName::ALERT_REOPEN->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-reopen-button');
    }

    public function shouldRender(): bool
    {
        return $this->canReopen;
    }
}
