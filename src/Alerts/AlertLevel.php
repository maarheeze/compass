<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum AlertLevel: string implements LabelledEnum
{
    case CRITICAL = 'critical';
    case NOTICE = 'notice';
    case WARNING = 'warning';

    public function labelKey(): string
    {
        return sprintf('compass::alert-level.%s', $this->name);
    }
}
