<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

readonly class GroupedAlertTypes
{
    /**
     * @param list<AlertType> $alertTypes
     */
    public function __construct(
        public AlertTypeGroup $alertTypeGroup,
        public array $alertTypes,
    ) {
    }
}
