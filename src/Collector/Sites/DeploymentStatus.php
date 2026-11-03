<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

enum DeploymentStatus: string
{
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
}
