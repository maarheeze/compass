<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

readonly class AlertLevelCount
{
    public function __construct(
        public AlertLevel $alertLevel,
        public int $count,
    ) {
    }
}
