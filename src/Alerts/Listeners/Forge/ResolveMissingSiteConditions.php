<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class ResolveMissingSiteConditions implements ShouldQueue
{
    private const array CLIENT_ALERT_TYPES = [
        AlertType::SITE_CLIENT_INCOMPATIBLE,
        AlertType::SITE_CLIENT_UNAUTHORIZED,
        AlertType::SITE_DEBUG_ENABLED,
        AlertType::SITE_NOT_IN_PRODUCTION,
    ];

    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SitesCollected $sitesCollected): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $missingSiteChunks = Site::query()
            ->notCollected()
            ->with('endpoints')
            ->lazyById()
            ->chunk(100);

        foreach ($missingSiteChunks as $missingSites) {
            $conditionObservations = new ConditionObservedCollection();

            foreach ($missingSites as $missingSite) {
                foreach (self::CLIENT_ALERT_TYPES as $clientAlertType) {
                    $conditionObservations->push($this->resolvedCondition($clientAlertType, $missingSite, $observedAt));
                }

                foreach ($missingSite->endpoints as $siteEndpoint) {
                    $conditionObservations->push(
                        $this->resolvedCondition(AlertType::SITE_ENDPOINT_DOWN, $siteEndpoint, $observedAt),
                    );
                }
            }

            $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
        }
    }

    private function resolvedCondition(
        AlertType $alertType,
        AlertSubject $subject,
        CarbonImmutable $observedAt,
    ): ConditionObserved {
        return new ConditionObserved($alertType, $subject, ConditionState::RESOLVED, $observedAt);
    }
}
