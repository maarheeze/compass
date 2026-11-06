<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LogicException;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentState;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentStateClassifier;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\BadgeType;

class DeploymentStateBadge extends Component
{
    public readonly DeploymentState $deploymentState;

    public function __construct(
        Site $site,
        DeploymentStateClassifier $deploymentStateClassifier,
        private readonly ViewFactory $view,
    ) {
        $this->deploymentState = $deploymentStateClassifier->classify($site);
    }

    public function badgeType(): BadgeType
    {
        return match ($this->deploymentState) {
            DeploymentState::BEHIND => BadgeType::WARNING,
            DeploymentState::DEPLOYING => BadgeType::INFO,
            DeploymentState::DEPLOYMENT_OVERDUE => BadgeType::DANGER,
            DeploymentState::UNKNOWN => throw new LogicException(),
            DeploymentState::UP_TO_DATE => BadgeType::SUCCESS,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.deployment-state-badge');
    }

    public function shouldRender(): bool
    {
        return $this->deploymentState !== DeploymentState::UNKNOWN;
    }
}
