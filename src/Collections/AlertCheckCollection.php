<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\AlertCheckStateClassifier;
use Maarheeze\Compass\Alerts\AlertCheckStateCount;
use Maarheeze\Compass\Models\AlertCheck;

/**
 * @extends Collection<array-key, AlertCheck>
 */
class AlertCheckCollection extends Collection
{
    private const array STATES_MOST_URGENT_FIRST = [
        AlertCheckState::FAILING,
        AlertCheckState::MUTED,
        AlertCheckState::OK,
    ];

    public function failing(AlertCheckStateClassifier $alertCheckStateClassifier): self
    {
        return $this->filter(static function (AlertCheck $alertCheck) use ($alertCheckStateClassifier): bool {
            return $alertCheckStateClassifier->classify($alertCheck) === AlertCheckState::FAILING;
        });
    }

    public function notFailing(AlertCheckStateClassifier $alertCheckStateClassifier): self
    {
        return $this->filter(static function (AlertCheck $alertCheck) use ($alertCheckStateClassifier): bool {
            return $alertCheckStateClassifier->classify($alertCheck) !== AlertCheckState::FAILING;
        });
    }

    public function stateCounts(AlertCheckStateClassifier $alertCheckStateClassifier): AlertCheckStateCountCollection
    {
        $alertCheckStateCounts = new AlertCheckStateCountCollection();

        foreach (self::STATES_MOST_URGENT_FIRST as $alertCheckState) {
            $count = $this
                ->filter(static function (AlertCheck $alertCheck) use ($alertCheckState, $alertCheckStateClassifier): bool {
                    return $alertCheckStateClassifier->classify($alertCheck) === $alertCheckState;
                })
                ->count();

            if ($count <= 0) {
                continue;
            }

            $alertCheckStateCounts->push(new AlertCheckStateCount($alertCheckState, $count));
        }

        return $alertCheckStateCounts;
    }
}
