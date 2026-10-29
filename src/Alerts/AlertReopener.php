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

readonly class AlertReopener
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
    ) {
    }

    public function canReopen(Alert $alert): bool
    {
        return $this->reopenableAlertQuery($alert)->exists();
    }

    /**
     * @throws Throwable
     */
    public function reopen(Alert $alert, User $user): void
    {
        $reopenableAlert = $this->reopenableAlertQuery($alert)->firstOrFail();
        $reopenedAt = CarbonImmutable::instance($this->clock->now());

        $this->connection->transaction(static function () use ($reopenableAlert, $reopenedAt, $user): void {
            $reopenableAlert->update([
                'status' => AlertStatus::OPEN,
                'done_at' => null,
            ]);
            $reopenableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::REOPENED,
                'occurred_at' => $reopenedAt,
                'user_id' => $user->id,
            ]);
        });
    }

    private function reopenableAlertQuery(Alert $alert): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->where('status', AlertStatus::DONE)
            ->whereNotExists(
                Alert::query()
                    ->where('subject_type', $alert->subject_type)
                    ->where('subject_id', $alert->subject_id)
                    ->where('type', $alert->type)
                    ->notDone(),
            );
    }
}
