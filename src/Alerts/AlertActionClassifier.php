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
                ConditionState::ACTIVE => AlertAction::OPEN,
                ConditionState::RESOLVED => null,
            };
        }

        if ($observedCondition === $storedAlert->condition) {
            return null;
        }

        return match ($observedCondition) {
            ConditionState::ACTIVE => AlertAction::RECUR,
            ConditionState::RESOLVED => AlertAction::RESOLVE,
        };
    }
}
