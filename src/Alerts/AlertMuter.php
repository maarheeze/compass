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
        return $this->muteableAlertQuery($alert, CarbonImmutable::instance($this->clock->now()))->exists();
    }

    public function canUnmute(Alert $alert): bool
    {
        return $this->unmuteableAlertQuery($alert, CarbonImmutable::instance($this->clock->now()))->exists();
    }

    /**
     * @throws Throwable
     */
    public function mute(Alert $alert, User $user): void
    {
        $mutedAt = CarbonImmutable::instance($this->clock->now());
        $muteableAlert = $this->muteableAlertQuery($alert, $mutedAt)->firstOrFail();

        $this->connection->transaction(static function () use ($mutedAt, $muteableAlert, $user): void {
            $muteableAlert->update(['muted_until' => $mutedAt->addHour()]);
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
        $unmutedAt = CarbonImmutable::instance($this->clock->now());
        $unmuteableAlert = $this->unmuteableAlertQuery($alert, $unmutedAt)->firstOrFail();

        $this->connection->transaction(static function () use ($unmutedAt, $unmuteableAlert, $user): void {
            $unmuteableAlert->update(['muted_until' => null]);
            $unmuteableAlert->historyEntries()->create([
                'type' => AlertHistoryEntryType::UNMUTED,
                'occurred_at' => $unmutedAt,
                'user_id' => $user->id,
            ]);
        });
    }

    private function muteableAlertQuery(Alert $alert, CarbonImmutable $now): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->notDone()
            ->notMuted($now);
    }

    private function unmuteableAlertQuery(Alert $alert, CarbonImmutable $now): AlertBuilder
    {
        return Alert::query()
            ->whereKey($alert->id)
            ->muted($now);
    }
}
