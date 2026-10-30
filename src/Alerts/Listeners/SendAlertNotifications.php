<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Notifications\Dispatcher;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\User;

readonly class SendAlertNotifications
{
    public function __construct(
        private Dispatcher $notifications,
    ) {
    }

    public function handle(AlertOpened $alertOpened): void
    {
        $subscribedUsers = User::query()
            ->subscribedTo($alertOpened->alert->type)
            ->lazyById();
        $alertOpenedNotification = new AlertOpenedNotification($alertOpened->alert);

        foreach ($subscribedUsers as $subscribedUser) {
            $this->notifications->send($subscribedUser, $alertOpenedNotification);
        }
    }
}
