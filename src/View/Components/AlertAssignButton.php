<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertAssigner;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertAssignButton extends Component
{
    public readonly string $assignUrl;
    public readonly bool $canAssignToMe;

    public function __construct(
        Alert $alert,
        AlertAssigner $alertAssigner,
        AuthenticatedUser $authenticatedUser,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->canAssignToMe = $alertAssigner->canAssignToMe($alert, $authenticatedUser->user());
        $this->assignUrl = $url->route(RouteName::ALERT_ASSIGN->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-assign-button');
    }

    public function shouldRender(): bool
    {
        return $this->canAssignToMe;
    }
}
