<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentState;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentStateClassifier;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class SiteBehindEvaluator
{
    public function __construct(
        private ClockInterface $clock,
        private DeploymentStateClassifier $deploymentStateClassifier,
    ) {
    }

    public function conditionObservation(Site $site): ConditionObservation
    {
        return new ConditionObservation(
            new SiteBehind(),
            $site,
            $this->observedCondition($site),
            CarbonImmutable::instance($this->clock->now()),
        );
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($this->deploymentStateClassifier->classify($site) === DeploymentState::BEHIND) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
