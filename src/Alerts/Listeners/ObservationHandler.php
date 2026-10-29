<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\WithoutOverlapping;
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
use Webmozart\Assert\Assert;

use function sprintf;

#[Tries(5)]
readonly class ObservationHandler implements ShouldQueue
{
    public function __construct(
        private AlertActionClassifier $alertActionClassifier,
        private ConnectionInterface $connection,
        private Dispatcher $events,
    ) {
    }

    public function handle(ConditionObserved $conditionObserved): void
    {
        $storedAlert = $conditionObserved->subject
            ->alerts()
            ->notDone()
            ->where('type', $conditionObserved->alertType)
            ->first();
        $alertAction = $this->alertActionClassifier->classify($conditionObserved->condition, $storedAlert);

        $this->connection->transaction(function () use ($alertAction, $conditionObserved, $storedAlert): void {
            $observedAlert = match ($alertAction) {
                AlertAction::OPEN => $this->open($conditionObserved),
                AlertAction::RECUR => $this->recur($conditionObserved, $storedAlert),
                AlertAction::RESOLVE => $this->resolve($conditionObserved, $storedAlert),
                null => $storedAlert,
            };

            $conditionObserved->subject
                ->observations()
                ->create([
                    'alert_id' => $observedAlert?->id,
                    'alert_type' => $conditionObserved->alertType,
                    'condition' => $conditionObserved->condition,
                    'observed_at' => $conditionObserved->observedAt,
                ]);
        });
    }

    /**
     * @return list<WithoutOverlapping>
     */
    public function middleware(ConditionObserved $conditionObserved): array
    {
        $alerts = $conditionObserved->subject->alerts();
        $subjectId = $alerts->getParentKey();
        Assert::isInstanceOf($subjectId, UuidInterface::class);
        $lockKey = sprintf(
            '%s:%s:%s',
            $conditionObserved->alertType->value,
            $alerts->getMorphClass(),
            $subjectId->toString(),
        );

        return [
            new WithoutOverlapping($lockKey)
                ->releaseAfter(5)
                ->expireAfter(60),
        ];
    }

    private function open(ConditionObserved $conditionObserved): Alert
    {
        $alert = $conditionObserved->subject
            ->alerts()
            ->create([
                'type' => $conditionObserved->alertType,
                'condition' => ConditionState::ACTIVE,
                'status' => AlertStatus::OPEN,
            ]);
        $alert->historyEntries()->create([
            'type' => AlertHistoryEntryType::OPENED,
            'occurred_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertOpened($alert));

        return $alert;
    }

    private function recur(ConditionObserved $conditionObserved, ?Alert $storedAlert): Alert
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => ConditionState::ACTIVE,
            'resolved_at' => null,
        ]);
        $storedAlert->historyEntries()->create([
            'type' => AlertHistoryEntryType::RECURRED,
            'occurred_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertRecurred($storedAlert));

        return $storedAlert;
    }

    private function resolve(ConditionObserved $conditionObserved, ?Alert $storedAlert): Alert
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => ConditionState::RESOLVED,
            'resolved_at' => $conditionObserved->observedAt,
        ]);
        $storedAlert->historyEntries()->create([
            'type' => AlertHistoryEntryType::RESOLVED,
            'occurred_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertResolved($storedAlert));

        return $storedAlert;
    }
}
