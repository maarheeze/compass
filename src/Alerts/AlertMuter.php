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

readonly class AlertMuter
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
    ) {
    }

    public function canMute(Alert $alert): bool
    {
        return $this->muteableAlertQuery($alert)->exists();
    }

    public function canUnmute(Alert $alert): bool
    {
        return $this->unmuteableAlertQuery($alert)->exists();
    }

    /**
     * @throws Throwable
     */
    public function mute(Alert $alert, User $user): void
    {
        $muteableAlert = $this->muteableAlertQuery($alert)->firstOrFail();
        $mutedAt = CarbonImmutable::instance($this->clock->now());

        $this->connection->transaction(static function () use ($mutedAt, $muteableAlert, $user): void {
            $muteableAlert->update(['is_muted' => true]);
            $muteableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::MUTED,
                'occurred_at' => $mutedAt,
                'user_id' => $user->id,
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function unmute(Alert $alert, User $user): void
    {
        $unmuteableAlert = $this->unmuteableAlertQuery($alert)->firstOrFail();
        $unmutedAt = CarbonImmutable::instance($this->clock->now());

        $this->connection->transaction(static function () use ($unmutedAt, $unmuteableAlert, $user): void {
            $unmuteableAlert->update(['is_muted' => false]);
            $unmuteableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::UNMUTED,
                'occurred_at' => $unmutedAt,
                'user_id' => $user->id,
            ]);
        });
    }

    private function muteableAlertQuery(Alert $alert): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->notDone()
            ->notMuted();
    }

    private function unmuteableAlertQuery(Alert $alert): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->muted();
    }
}
