<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Maarheeze\Compass\Alerts\AlertAction;
use Maarheeze\Compass\Alerts\AlertActionClassifier;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\Events\AlertCleared;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\AlertRecurred;
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
        private Dispatcher $events,
    ) {
    }

    public function handle(ConditionObserved $conditionObserved): void
    {
        $storedAlert = $conditionObserved->subject
            ->alerts()
            ->where('type', $conditionObserved->alertType)
            ->first();
        $alertAction = $this->alertActionClassifier->classify($conditionObserved->condition, $storedAlert);

        match ($alertAction) {
            AlertAction::CLEAR => $this->clear($conditionObserved, $storedAlert),
            AlertAction::OPEN => $this->open($conditionObserved),
            AlertAction::RECUR => $this->recur($storedAlert),
            null => null,
        };
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

    private function clear(ConditionObserved $conditionObserved, ?Alert $storedAlert): void
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => AlertCondition::GONE,
            'gone_at' => $conditionObserved->observedAt,
        ]);

        $this->events->dispatch(new AlertCleared($storedAlert));
    }

    private function open(ConditionObserved $conditionObserved): void
    {
        $alert = $conditionObserved->subject
            ->alerts()
            ->create([
                'type' => $conditionObserved->alertType,
                'condition' => AlertCondition::PRESENT,
                'status' => AlertStatus::OPEN,
            ]);

        $this->events->dispatch(new AlertOpened($alert));
    }

    private function recur(?Alert $storedAlert): void
    {
        Assert::isInstanceOf($storedAlert, Alert::class);
        $storedAlert->update([
            'condition' => AlertCondition::PRESENT,
            'gone_at' => null,
        ]);

        $this->events->dispatch(new AlertRecurred($storedAlert));
    }
}
