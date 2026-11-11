<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum DeploymentStatus: string implements LabelledEnum
{
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';

    public function labelKey(): string
    {
        return sprintf('compass::deployment-status.%s', $this->name);
    }
}
