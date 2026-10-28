<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeDefinitions;
use Maarheeze\Compass\Models\Alert;

class AlertRow extends Component
{
    public readonly CarbonImmutable $createdAt;
    public readonly string $description;
    public readonly AlertType $type;

    public function __construct(
        Alert $alert,
        AlertTypeDefinitions $alertTypeDefinitions,
        private readonly ViewFactory $view,
    ) {
        $this->createdAt = $alert->created_at;
        $this->description = $alertTypeDefinitions->definition($alert->type)->description($alert);
        $this->type = $alert->type;
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-row');
    }
}
