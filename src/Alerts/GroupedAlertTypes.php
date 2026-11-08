<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\Collections\AlertTypeCollection;

readonly class GroupedAlertTypes
{
    public function __construct(
        public AlertTypeGroup $alertTypeGroup,
        public AlertTypeCollection $alertTypes,
    ) {
    }
}
