<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Alerts\AlertAction;
use Maarheeze\Compass\Alerts\AlertActionClassifier;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\AlertRecurred;
use Maarheeze\Compass\Alerts\Events\AlertResolved;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Uuid\UuidInterface;
use Throwable;
use Webmozart\Assert\Assert;

use function sprintf;

readonly class ObservationHandler
{
    public function __construct(
        private AlertActionClassifier $alertActionClassifier,
        private CacheRepository $cache,
        private ConnectionInterface $connection,
        private Dispatcher $events,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(ConditionObserved $conditionObserved): void
    {
        Assert::isInstanceOf($this->cache->getStore(), LockProvider::class)
            ->lock($this->lockKey($conditionObserved), 60)
            ->block(5, function () use ($conditionObserved): void {
                $this->observe($conditionObserved);
            });
    }

    private function lockKey(ConditionObserved $conditionObserved): string
    {
        $alerts = $conditionObserved->subject->alerts();
        $subjectId = $alerts->getParentKey();
        Assert::isInstanceOf($subjectId, UuidInterface::class);

        return sprintf(
            '%s:%s:%s',
            $conditionObserved->alertType->value,
            $alerts->getMorphClass(),
            $subjectId->toString(),
        );
    }

    /**
     * @throws Throwable
     */
    private function observe(ConditionObserved $conditionObserved): void
    {
        $storedAlert = $conditionObserved->subject
            ->alerts()
            ->notDone()
            ->ofType($conditionObserved->alertType)
            ->first();
        $alertAction = $this->alertActionClassifier->classify($conditionObserved->condition, $storedAlert);

        $this->connection->transaction(function () use ($alertAction, $conditionObserved, $storedAlert): void {
            match ($alertAction) {
                AlertAction::OPEN => $this->open($conditionObserved),
                AlertAction::RECUR => $this->recur($conditionObserved, $storedAlert),
                AlertAction::RESOLVE => $this->resolve($conditionObserved, $storedAlert),
                null => $storedAlert?->update(['last_observed_at' => $conditionObserved->observedAt]),
            };
        });
    }

    private function open(ConditionObserved $conditionObserved): void
    {
        $alert = $conditionObserved->subject
            ->alerts()
            ->create([
                'type' => $conditionObserved->alertType,
                'condition' => ConditionState::ACTIVE,
                'status' => AlertStatus::OPEN,
                'is_muted' => false,
                'last_observed_at' => $conditionObserved->observedAt,
            ]);
        $alert->historyEntries()->create([
            'type' => AlertHistoryEntryType::OPENED,
            'occurred_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertOpened($alert));
    }

    private function recur(ConditionObserved $conditionObserved, ?Alert $storedAlert): void
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => ConditionState::ACTIVE,
            'resolved_at' => null,
            'last_observed_at' => $conditionObserved->observedAt,
        ]);
        $storedAlert->historyEntries()->create([
            'type' => AlertHistoryEntryType::RECURRED,
            'occurred_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertRecurred($storedAlert));
    }

    private function resolve(ConditionObserved $conditionObserved, ?Alert $storedAlert): void
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => ConditionState::RESOLVED,
            'resolved_at' => $conditionObserved->observedAt,
            'last_observed_at' => $conditionObserved->observedAt,
        ]);
        $storedAlert->historyEntries()->create([
            'type' => AlertHistoryEntryType::RESOLVED,
            'occurred_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertResolved($storedAlert));
    }
}
