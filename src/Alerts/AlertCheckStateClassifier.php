<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use LogicException;
use Maarheeze\Compass\Models\AlertCheck;

readonly class AlertCheckStateClassifier
{
    public function classify(AlertCheck $alertCheck): AlertCheckState
    {
        return match ($alertCheck->condition) {
            ConditionState::ACTIVE => $this->classifyActive($alertCheck),
            ConditionState::NOT_APPLICABLE => throw new LogicException('a check that does not apply has no state'),
            ConditionState::RESOLVED => AlertCheckState::OK,
        };
    }

    private function classifyActive(AlertCheck $alertCheck): AlertCheckState
    {
        if ($alertCheck->alert_is_muted === true) {
            return AlertCheckState::MUTED;
        }

        return AlertCheckState::FAILING;
    }
}
