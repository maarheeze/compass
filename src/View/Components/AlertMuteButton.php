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

class AlertMuteButton extends Component
{
    public readonly bool $canMute;
    public readonly string $muteUrl;

    public function __construct(
        Alert $alert,
        AlertMuter $alertMuter,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->canMute = $alertMuter->canMute($alert);
        $this->muteUrl = $url->route(RouteName::ALERT_MUTE->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-mute-button');
    }

    public function shouldRender(): bool
    {
        return $this->canMute;
    }
}
