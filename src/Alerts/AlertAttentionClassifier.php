<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\Models\Builders\AlertBuilder;

readonly class AlertAttentionClassifier
{
    public function classify(AlertBuilder $alertQuery): AlertAttention
    {
        $alertsNotDoneQuery = $alertQuery
            ->clone()
            ->notDone();

        if (!$alertsNotDoneQuery->exists()) {
            return AlertAttention::NONE;
        }

        if ($alertsNotDoneQuery->notMuted()->exists()) {
            return AlertAttention::ACTIVE;
        }

        return AlertAttention::MUTED;
    }
}
