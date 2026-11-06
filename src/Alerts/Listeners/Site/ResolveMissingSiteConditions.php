<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Site;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
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
            ->ofSource($sitesCollected->source)
            ->notCollected()
            ->with('reports.endpoints')
            ->lazyById()
            ->chunk(100);

        foreach ($missingSiteChunks as $missingSites) {
            $conditionObservations = new ConditionObservationCollection();

            foreach ($missingSites as $missingSite) {
                foreach ($missingSite->reports as $siteReport) {
                    $conditionObservations = $conditionObservations->merge(
                        $this->resolvedSiteReportConditions($siteReport, $observedAt),
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
    ): ConditionObservation {
        return new ConditionObservation($alertType, $subject, ConditionState::RESOLVED, $observedAt);
    }

    private function resolvedSiteReportConditions(
        SiteReport $siteReport,
        CarbonImmutable $observedAt,
    ): ConditionObservationCollection {
        $conditionObservations = new ConditionObservationCollection();

        foreach (self::CLIENT_ALERT_TYPES as $clientAlertType) {
            $conditionObservations->push($this->resolvedCondition($clientAlertType, $siteReport, $observedAt));
        }

        foreach ($siteReport->endpoints as $siteEndpoint) {
            $conditionObservations->push(
                $this->resolvedCondition(AlertType::SITE_ENDPOINT_DOWN, $siteEndpoint, $observedAt),
            );
        }

        return $conditionObservations;
    }
}
