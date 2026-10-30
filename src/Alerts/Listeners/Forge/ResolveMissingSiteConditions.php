<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class ResolveMissingSiteConditions implements ShouldQueue
{
    private const array CLIENT_ALERT_TYPES = [
        AlertType::DEBUG_ENABLED,
        AlertType::INCOMPATIBLE_CLIENT,
        AlertType::NON_PRODUCTION,
        AlertType::UNAUTHORIZED_CLIENT,
    ];

    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(ForgeRunSucceeded $forgeRunSucceeded): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $missingSites = Site::query()
            ->notCollected()
            ->with('endpoints')
            ->get();

        foreach ($missingSites as $missingSite) {
            foreach (self::CLIENT_ALERT_TYPES as $clientAlertType) {
                $this->observeResolved($clientAlertType, $missingSite, $observedAt);
            }

            foreach ($missingSite->endpoints as $siteEndpoint) {
                $this->observeResolved(AlertType::DOWN_SITE_ENDPOINT, $siteEndpoint, $observedAt);
            }
        }
    }

    private function observeResolved(AlertType $alertType, AlertSubject $subject, CarbonImmutable $observedAt): void
    {
        $this->events->dispatch(new ConditionObserved(
            $alertType,
            $subject,
            ConditionState::RESOLVED,
            $observedAt,
        ));
    }
}
