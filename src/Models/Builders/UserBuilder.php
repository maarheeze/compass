<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\User;

/**
 * @extends Builder<User>
 */
class UserBuilder extends Builder
{
    public function subscribedTo(AlertType $alertType): self
    {
        return $this->whereHas('notificationSubscriptions', static function (Builder $notificationSubscriptionQuery) use ($alertType): void {
            $notificationSubscriptionQuery->where('alert_type_identifier', $alertType->identifier());
        });
    }
}
