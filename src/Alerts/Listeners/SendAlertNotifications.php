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
        $this->notifications->send(User::query()->get(), new AlertOpenedNotification($alertOpened->alert));
    }
}
