<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Deployment;

enum DeploymentState
{
    case BEHIND;
    case DEPLOYING;
    case DEPLOYMENT_OVERDUE;
    case UNKNOWN;
    case UP_TO_DATE;
}
