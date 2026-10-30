<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\User;
use Throwable;

readonly class NotificationSubscriptionReplacer
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function replace(User $user, NotificationSubscriptionCollection $notificationSubscriptions): void
    {
        $this->connection->transaction(static function () use ($notificationSubscriptions, $user): void {
            User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->sole();
            $user->notificationSubscriptions()->delete();
            $user->notificationSubscriptions()->saveMany($notificationSubscriptions);
        });
    }
}
