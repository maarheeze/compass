<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Maarheeze\Compass\Broadcasting\CompassChannel;

readonly class AlertChanged implements ShouldBroadcast
{
    public function __construct(
        public ?string $toastHtml,
    ) {
    }

    public function broadcastAs(): string
    {
        return 'alert-changed';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(CompassChannel::NAME);
    }
}
