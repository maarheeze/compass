<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\Models\Alert;

readonly class AlertActionClassifier
{
    public function classify(ConditionState $observedCondition, ?Alert $storedAlert): ?AlertAction
    {
        if ($storedAlert === null) {
            return match ($observedCondition) {
                ConditionState::PRESENT => AlertAction::OPEN,
                ConditionState::GONE => null,
            };
        }

        if ($observedCondition === $storedAlert->condition) {
            return null;
        }

        return match ($observedCondition) {
            ConditionState::PRESENT => AlertAction::RECUR,
            ConditionState::GONE => AlertAction::CLEAR,
        };
    }
}
