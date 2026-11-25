<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

readonly class QueueNames
{
    public function __construct(
        private ?string $highQueue,
        private ?string $lowQueue,
    ) {
    }

    public function forPriority(QueuePriority $queuePriority): ?string
    {
        return match ($queuePriority) {
            QueuePriority::HIGH => $this->highQueue,
            QueuePriority::LOW => $this->lowQueue,
            QueuePriority::MEDIUM => null,
        };
    }
}
