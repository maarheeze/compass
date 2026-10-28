<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\Models\Alert;

readonly class AlertActionClassifier
{
    public function classify(AlertCondition $observedCondition, ?Alert $storedAlert): ?AlertAction
    {
        if ($storedAlert === null) {
            return match ($observedCondition) {
                AlertCondition::PRESENT => AlertAction::OPEN,
                AlertCondition::GONE => null,
            };
        }

        if ($observedCondition === $storedAlert->condition) {
            return null;
        }

        return match ($observedCondition) {
            AlertCondition::PRESENT => AlertAction::RECUR,
            AlertCondition::GONE => AlertAction::CLEAR,
        };
    }
}
