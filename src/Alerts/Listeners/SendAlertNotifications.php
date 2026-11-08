<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Notifications\Dispatcher;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\User;

readonly class SendAlertNotifications
{
    public function __construct(
        private AlertTypes $alertTypes,
        private Dispatcher $notifications,
    ) {
    }

    public function handle(AlertOpened $alertOpened): void
    {
        $alertType = $this->alertTypes->alertType($alertOpened->alert->alert_type_identifier);
        $subscribedUsers = User::query()
            ->subscribedTo($alertType)
            ->lazyById();
        $alertOpenedNotification = new AlertOpenedNotification($alertOpened->alert, $alertType);

        foreach ($subscribedUsers as $subscribedUser) {
            $this->notifications->send($subscribedUser, $alertOpenedNotification);
        }
    }
}
