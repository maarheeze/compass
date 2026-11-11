<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Deployment;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum DeploymentState implements LabelledEnum
{
    case BEHIND;
    case DEPLOYING;
    case DEPLOYMENT_OVERDUE;
    case UNKNOWN;
    case UP_TO_DATE;

    public function labelKey(): string
    {
        return sprintf('compass::deployment-state.%s', $this->name);
    }
}
