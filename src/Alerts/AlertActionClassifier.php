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
                ConditionState::NOT_APPLICABLE, ConditionState::RESOLVED => null,
            };
        }

        return match ($storedAlert->condition) {
            ConditionState::ACTIVE => match ($observedCondition) {
                ConditionState::ACTIVE => null,
                ConditionState::NOT_APPLICABLE, ConditionState::RESOLVED => AlertAction::RESOLVE,
            },
            ConditionState::NOT_APPLICABLE, ConditionState::RESOLVED => match ($observedCondition) {
                ConditionState::ACTIVE => AlertAction::RECUR,
                ConditionState::NOT_APPLICABLE, ConditionState::RESOLVED => null,
            },
        };
    }
}
