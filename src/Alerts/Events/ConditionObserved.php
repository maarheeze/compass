<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Events;

use Carbon\CarbonImmutable;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;

readonly class ConditionObserved
{
    use SerializesModels;

    public function __construct(
        public AlertType $alertType,
        public AlertSubject $subject,
        public AlertCondition $condition,
        public CarbonImmutable $observedAt,
    ) {
    }
}
