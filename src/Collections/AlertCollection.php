<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertLevelCount;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Models\Alert;

use function usort;

/**
 * @extends Collection<array-key, Alert>
 */
class AlertCollection extends Collection
{
    public function done(): self
    {
        return $this->filter(static function (Alert $alert): bool {
            return $alert->status === AlertStatus::DONE;
        });
    }

    public function levelCounts(AlertTypes $alertTypes): AlertLevelCountCollection
    {
        $alertLevels = AlertLevel::cases();
        usort($alertLevels, static function (AlertLevel $alertLevel, AlertLevel $otherAlertLevel): int {
            return $alertLevel->order() <=> $otherAlertLevel->order();
        });
        $alertLevelCounts = new AlertLevelCountCollection();

        foreach ($alertLevels as $alertLevel) {
            $count = $this
                ->filter(static function (Alert $alert) use ($alertLevel, $alertTypes): bool {
                    return self::alertLevel($alert, $alertTypes) === $alertLevel;
                })
                ->count();

            if ($count <= 0) {
                continue;
            }

            $alertLevelCounts->push(new AlertLevelCount($alertLevel, $count));
        }

        return $alertLevelCounts;
    }

    public function mutedOpen(): self
    {
        return $this->filter(static function (Alert $alert): bool {
            return $alert->is_muted && $alert->status === AlertStatus::OPEN;
        });
    }

    public function mutedOrDone(): self
    {
        return $this->filter(static function (Alert $alert): bool {
            return $alert->is_muted || $alert->status === AlertStatus::DONE;
        });
    }

    public function sortedByLevel(AlertTypes $alertTypes): self
    {
        return $this
            ->sortBy(static function (Alert $alert) use ($alertTypes): int {
                return self::alertLevel($alert, $alertTypes)->order();
            })
            ->values();
    }

    public function unmutedOpen(): self
    {
        return $this->filter(static function (Alert $alert): bool {
            return !$alert->is_muted && $alert->status === AlertStatus::OPEN;
        });
    }

    private static function alertLevel(Alert $alert, AlertTypes $alertTypes): AlertLevel
    {
        return $alertTypes->alertType($alert->alert_type_identifier)->level();
    }
}
