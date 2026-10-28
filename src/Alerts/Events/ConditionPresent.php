<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Events;

use Carbon\CarbonImmutable;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;

readonly class ConditionPresent
{
    use SerializesModels;

    public function __construct(
        public AlertType $alertType,
        public AlertSubject $subject,
        public CarbonImmutable $observedAt,
    ) {
    }
}
