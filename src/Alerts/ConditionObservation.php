<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\SerializesModels;

readonly class ConditionObservation
{
    use SerializesModels;

    public function __construct(
        public AlertType $alertType,
        #[WithoutRelations]
        public AlertSubject $subject,
        public ConditionState $condition,
        public CarbonImmutable $observedAt,
    ) {
    }
}
