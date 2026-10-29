<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertMuter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertUnmuteButton extends Component
{
    public readonly bool $canUnmute;
    public readonly string $unmuteUrl;

    public function __construct(
        Alert $alert,
        AlertMuter $alertMuter,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->canUnmute = $alertMuter->canUnmute($alert);
        $this->unmuteUrl = $url->route(RouteName::ALERT_UNMUTE->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-unmute-button');
    }

    public function shouldRender(): bool
    {
        return $this->canUnmute;
    }
}
