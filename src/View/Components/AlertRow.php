<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeDefinitions;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;

class AlertRow extends Component
{
    public readonly CarbonImmutable $createdAt;
    public readonly string $description;
    public readonly AlertType $type;
    public readonly string $url;

    public function __construct(
        Alert $alert,
        AlertTypeDefinitions $alertTypeDefinitions,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->createdAt = $alert->created_at;
        $this->description = $alertTypeDefinitions->definition($alert->type)->description($alert);
        $this->type = $alert->type;
        $this->url = $url->route(RouteName::ALERT->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-row');
    }
}
