<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

use function in_array;

/**
 * @extends Collection<int, AlertType>
 */
class AlertTypeCollection extends Collection
{
    /**
     * @return list<AlertTypeGroup>
     */
    public function alertTypeGroups(): array
    {
        $alertTypeGroups = [];

        foreach ($this->items as $alertType) {
            if (!in_array($alertType->group(), $alertTypeGroups, true)) {
                $alertTypeGroups[] = $alertType->group();
            }
        }

        return $alertTypeGroups;
    }

    public function ofGroup(AlertTypeGroup $alertTypeGroup): self
    {
        return $this
            ->filter(static function (AlertType $alertType) use ($alertTypeGroup): bool {
                return $alertType->group() === $alertTypeGroup;
            })
            ->values();
    }
}
