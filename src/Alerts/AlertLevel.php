<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function array_flip;
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

    public function order(): int
    {
        $ordering = [
            self::CRITICAL->value,
            self::WARNING->value,
            self::NOTICE->value,
        ];

        return array_flip($ordering)[$this->value];
    }
}
