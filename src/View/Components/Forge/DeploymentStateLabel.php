<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\Forge;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Models\Site;

class DeploymentStateLabel extends Component
{
    public readonly DeploymentState $deploymentState;

    public function __construct(
        Site $site,
        DeploymentStateClassifier $deploymentStateClassifier,
        private readonly ViewFactory $view,
    ) {
        $this->deploymentState = $deploymentStateClassifier->classify($site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.forge.deployment-state-label');
    }
}
