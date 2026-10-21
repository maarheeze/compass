<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

enum DeploymentState
{
    case BEHIND;
    case UNKNOWN;
    case UP_TO_DATE;
}
