<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertDoneMarker;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertMarkDoneButton extends Component
{
    public readonly bool $canMarkDone;
    public readonly string $markDoneUrl;

    public function __construct(
        Alert $alert,
        AlertDoneMarker $alertDoneMarker,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->canMarkDone = $alertDoneMarker->canMarkDone($alert);
        $this->markDoneUrl = $url->route(RouteName::ALERT_MARK_DONE->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-mark-done-button');
    }

    public function shouldRender(): bool
    {
        return $this->canMarkDone;
    }
}
