<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Builders\AlertBuilder;
use Maarheeze\Compass\Models\User;
use Psr\Clock\ClockInterface;
use Throwable;

readonly class AlertDoneMarker
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
    ) {
    }

    public function canMarkDone(Alert $alert): bool
    {
        return $this->markableAlertQuery($alert)->exists();
    }

    /**
     * @throws Throwable
     */
    public function markDone(Alert $alert, User $user): void
    {
        $markableAlert = $this->markableAlertQuery($alert)->firstOrFail();
        $doneAt = CarbonImmutable::instance($this->clock->now());

        $this->connection->transaction(static function () use ($doneAt, $markableAlert, $user): void {
            $markableAlert->update([
                'status' => AlertStatus::DONE,
                'done_at' => $doneAt,
            ]);
            $markableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::MARKED_DONE,
                'occurred_at' => $doneAt,
                'user_id' => $user->id,
            ]);
        });
    }

    private function markableAlertQuery(Alert $alert): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->notDone()
            ->where('condition', ConditionState::RESOLVED);
    }
}
