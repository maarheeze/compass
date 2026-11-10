<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Maarheeze\Compass\Broadcasting\CompassChannel;

readonly class RunStatusChanged implements ShouldBroadcast
{
    public function __construct(
        public ?string $toastHtml,
    ) {
    }

    public function broadcastAs(): string
    {
        return 'run-status-changed';
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(CompassChannel::NAME);
    }
}
