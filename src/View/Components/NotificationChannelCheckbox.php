<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;

class NotificationChannelCheckbox extends Component
{
    public readonly bool $isChosen;

    public function __construct(
        public readonly AlertType $alertType,
        public readonly NotificationChannel $notificationChannel,
        NotificationSubscriptionCollection $notificationSubscriptions,
        private readonly ViewFactory $view,
    ) {
        $this->isChosen = $notificationSubscriptions->hasSubscription($alertType, $notificationChannel);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.notification-channel-checkbox');
    }
}
