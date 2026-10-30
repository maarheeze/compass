<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Models\NotificationSubscription;

/**
 * @extends Collection<array-key, NotificationSubscription>
 */
class NotificationSubscriptionCollection extends Collection
{
    public function hasSubscription(AlertType $alertType, NotificationChannel $notificationChannel): bool
    {
        return $this
            ->where('alert_type', $alertType)
            ->where('channel', $notificationChannel)
            ->isNotEmpty();
    }
}
