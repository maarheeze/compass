<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Events;

use Carbon\CarbonImmutable;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;

readonly class ConditionObserved
{
    use SerializesModels;

    public function __construct(
        public AlertType $alertType,
        public AlertSubject $subject,
        public ConditionState $condition,
        public CarbonImmutable $observedAt,
    ) {
    }
}
