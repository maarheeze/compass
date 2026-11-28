<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\Types\SiteQueueWorkerNotRunning;
use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealth;
use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealthClassifier;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class SiteQueueWorkerNotRunningEvaluator
{
    public function __construct(
        private ClockInterface $clock,
        private QueueWorkerHealthClassifier $queueWorkerHealthClassifier,
    ) {
    }

    public function conditionObservation(Site $site): ?ConditionObservation
    {
        $observedCondition = $this->observedCondition($this->queueWorkerHealthClassifier->classify($site));

        if ($observedCondition === null) {
            return null;
        }

        return new ConditionObservation(
            new SiteQueueWorkerNotRunning(),
            $site,
            $observedCondition,
            CarbonImmutable::instance($this->clock->now()),
        );
    }

    private function observedCondition(QueueWorkerHealth $queueWorkerHealth): ?ConditionState
    {
        return match ($queueWorkerHealth) {
            QueueWorkerHealth::NOT_RUNNING => ConditionState::ACTIVE,
            QueueWorkerHealth::NOT_NEEDED => ConditionState::NOT_APPLICABLE,
            QueueWorkerHealth::RUNNING => ConditionState::RESOLVED,
            QueueWorkerHealth::UNKNOWN => null,
        };
    }
}
