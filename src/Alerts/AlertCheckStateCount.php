<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

readonly class AlertCheckStateCount
{
    public function __construct(
        public AlertCheckState $alertCheckState,
        public int $count,
    ) {
    }
}
