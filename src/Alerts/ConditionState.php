<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum ConditionState: string implements LabelledEnum
{
    case ACTIVE = 'active';
    case NOT_APPLICABLE = 'not_applicable';
    case RESOLVED = 'resolved';

    public function labelKey(): string
    {
        return sprintf('compass::condition-state.%s', $this->name);
    }
}
