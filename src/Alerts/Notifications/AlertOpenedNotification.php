<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;

use function array_map;

class AlertOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Alert $alert,
        public readonly AlertType $alertType,
    ) {
    }

    public function toMail(User $notifiable): AlertOpenedMailable
    {
        return new AlertOpenedMailable($this->alert, $this->alertType)->to($notifiable->email);
    }

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return array_map(
            static function (NotificationChannel $notificationChannel): string {
                return $notificationChannel->value;
            },
            $notifiable->notificationSubscriptions->notificationChannels($this->alertType),
        );
    }
}
