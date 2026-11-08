<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use LogicException;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\AlertRecurred;
use Maarheeze\Compass\Alerts\Events\AlertResolved;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Uuid\UuidInterface;
use Throwable;
use Webmozart\Assert\Assert;

use function sprintf;

readonly class ObservationHandler
{
    public function __construct(
        private AlertActionClassifier $alertActionClassifier,
        private AlertTypes $alertTypes,
        private CacheRepository $cache,
        private ConnectionInterface $connection,
        private Dispatcher $events,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(ConditionObservation $conditionObservation): void
    {
        if (!$this->alertTypes->isRegistered($conditionObservation->alertType)) {
            throw new LogicException('unregistered alert type');
        }

        Assert::isInstanceOf($this->cache->getStore(), LockProvider::class)
            ->lock($this->lockKey($conditionObservation), 60)
            ->block(5, function () use ($conditionObservation): void {
                $this->observe($conditionObservation);
            });
    }

    private function lockKey(ConditionObservation $conditionObservation): string
    {
        $alerts = $conditionObservation->subject->alerts();
        $subjectId = $alerts->getParentKey();
        Assert::isInstanceOf($subjectId, UuidInterface::class);

        return sprintf(
            '%s:%s:%s',
            $conditionObservation->alertType->identifier(),
            $alerts->getMorphClass(),
            $subjectId->toString(),
        );
    }

    /**
     * @throws Throwable
     */
    private function observe(ConditionObservation $conditionObservation): void
    {
        $storedAlert = $conditionObservation->subject
            ->alerts()
            ->notDone()
            ->ofType($conditionObservation->alertType)
            ->first();
        $alertAction = $this->alertActionClassifier->classify($conditionObservation->condition, $storedAlert);

        if ($alertAction === null) {
            return;
        }

        $this->connection->transaction(function () use ($alertAction, $conditionObservation, $storedAlert): void {
            match ($alertAction) {
                AlertAction::OPEN => $this->open($conditionObservation),
                AlertAction::RECUR => $this->recur($conditionObservation, $storedAlert),
                AlertAction::RESOLVE => $this->resolve($conditionObservation, $storedAlert),
            };
        });
    }

    private function open(ConditionObservation $conditionObservation): void
    {
        $alert = $conditionObservation->subject
            ->alerts()
            ->create([
                'alert_type_identifier' => $conditionObservation->alertType->identifier(),
                'condition' => ConditionState::ACTIVE,
                'status' => AlertStatus::OPEN,
                'is_muted' => false,
            ]);
        $alert->historyEntries()->create([
            'type' => AlertHistoryEntryType::OPENED,
            'occurred_at' => $conditionObservation->observedAt,
        ]);

        $this->events->dispatch(new AlertOpened($alert));
    }

    private function recur(ConditionObservation $conditionObservation, ?Alert $storedAlert): void
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => ConditionState::ACTIVE,
            'resolved_at' => null,
        ]);
        $storedAlert->historyEntries()->create([
            'type' => AlertHistoryEntryType::RECURRED,
            'occurred_at' => $conditionObservation->observedAt,
        ]);

        $this->events->dispatch(new AlertRecurred($storedAlert));
    }

    private function resolve(ConditionObservation $conditionObservation, ?Alert $storedAlert): void
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => ConditionState::RESOLVED,
            'resolved_at' => $conditionObservation->observedAt,
        ]);
        $storedAlert->historyEntries()->create([
            'type' => AlertHistoryEntryType::RESOLVED,
            'occurred_at' => $conditionObservation->observedAt,
        ]);

        $this->events->dispatch(new AlertResolved($storedAlert));
    }
}
