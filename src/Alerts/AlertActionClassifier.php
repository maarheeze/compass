<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\Models\Alert;

readonly class AlertActionClassifier
{
    public function classify(AlertCondition $observedCondition, ?Alert $storedAlert): ?AlertAction
    {
        if ($observedCondition === AlertCondition::PRESENT && $storedAlert === null) {
            return AlertAction::OPEN;
        }

        return null;
    }
}
