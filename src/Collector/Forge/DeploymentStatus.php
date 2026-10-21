<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

enum DeploymentStatus: string
{
    case CANCELLED = 'cancelled';
    case DEPLOYING = 'deploying';
    case FAILED = 'failed';
    case FAILED_BUILD = 'failed-build';
    case FINISHED = 'finished';
    case PENDING = 'pending';
    case QUEUED = 'queued';
}
