<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum NotificationChannel: string implements LabelledEnum
{
    case MAIL = 'mail';

    public function labelKey(): string
    {
        return sprintf('compass::notification-channel.%s', $this->name);
    }
}
