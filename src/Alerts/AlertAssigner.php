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

readonly class AlertAssigner
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function assignToMe(Alert $alert, User $user): void
    {
        $assignableAlert = $this->assignableAlertQuery($alert, $user)->firstOrFail();
        $assignedAt = CarbonImmutable::instance($this->clock->now());

        $this->connection->transaction(static function () use ($assignableAlert, $assignedAt, $user): void {
            $assignableAlert->update(['assignee_id' => $user->id]);
            $assignableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::ASSIGNED,
                'occurred_at' => $assignedAt,
                'user_id' => $user->id,
            ]);
        });
    }

    public function canAssignToMe(Alert $alert, User $user): bool
    {
        return $this->assignableAlertQuery($alert, $user)->exists();
    }

    public function canUnassign(Alert $alert): bool
    {
        return $this->unassignableAlertQuery($alert)->exists();
    }

    /**
     * @throws Throwable
     */
    public function unassign(Alert $alert, User $user): void
    {
        $unassignableAlert = $this->unassignableAlertQuery($alert)->firstOrFail();
        $unassignedAt = CarbonImmutable::instance($this->clock->now());

        $this->connection->transaction(static function () use ($unassignableAlert, $unassignedAt, $user): void {
            $unassignableAlert->update(['assignee_id' => null]);
            $unassignableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::UNASSIGNED,
                'occurred_at' => $unassignedAt,
                'user_id' => $user->id,
            ]);
        });
    }

    private function assignableAlertQuery(Alert $alert, User $user): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->notDone()
            ->where(static function (AlertBuilder $alertQuery) use ($user): void {
                $alertQuery
                    ->whereNull('assignee_id')
                    ->orWhereNot('assignee_id', $user->id);
            });
    }

    private function unassignableAlertQuery(Alert $alert): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->whereNotNull('assignee_id');
    }
}
