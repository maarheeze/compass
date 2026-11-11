<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum AlertStatus: string implements LabelledEnum
{
    case DONE = 'done';
    case OPEN = 'open';

    public function labelKey(): string
    {
        return sprintf('compass::alert-status.%s', $this->name);
    }
}
