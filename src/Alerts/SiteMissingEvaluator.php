<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\Types\SiteMissing;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class SiteMissingEvaluator
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function conditionObservation(Site $site): ConditionObservation
    {
        return new ConditionObservation(
            new SiteMissing(),
            $site,
            $this->observedCondition($site),
            CarbonImmutable::instance($this->clock->now()),
        );
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($site->missingState() === MissingState::MISSING) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
