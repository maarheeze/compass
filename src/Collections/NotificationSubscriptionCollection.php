<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Models\NotificationSubscription;

use function array_values;

/**
 * @extends Collection<array-key, NotificationSubscription>
 */
class NotificationSubscriptionCollection extends Collection
{
    public function hasSubscription(AlertType $alertType, NotificationChannel $notificationChannel): bool
    {
        return $this
            ->where('alert_type_identifier', $alertType->identifier())
            ->where('channel', $notificationChannel)
            ->isNotEmpty();
    }

    /**
     * @return list<NotificationChannel>
     */
    public function notificationChannels(AlertType $alertType): array
    {
        return array_values($this
            ->where('alert_type_identifier', $alertType->identifier())
            ->map(static function (NotificationSubscription $notificationSubscription): NotificationChannel {
                return $notificationSubscription->channel;
            })
            ->all());
    }
}
