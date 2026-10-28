<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateMissingSites implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(ForgeRunSucceeded $forgeRunSucceeded): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        foreach (Site::query()->get() as $site) {
            $observedCondition = match ($site->missing_at !== null && $site->missing_ignored_at === null) {
                true => AlertCondition::PRESENT,
                false => AlertCondition::GONE,
            };

            $this->events->dispatch(new ConditionObserved(
                AlertType::MISSING_SITE,
                $site,
                $observedCondition,
                $observedAt,
            ));
        }
    }
}
