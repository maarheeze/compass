<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\DeploymentStatusBadgeType;

class DeploymentStatusBadge extends Component
{
    public readonly BadgeType $badgeType;

    public function __construct(
        public readonly DeploymentStatus $status,
        DeploymentStatusBadgeType $deploymentStatusBadgeType,
        private readonly ViewFactory $view,
    ) {
        $this->badgeType = $deploymentStatusBadgeType->badgeType($status);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.deployment-status-badge');
    }
}
