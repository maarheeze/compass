<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Models\Alert;

/**
 * @extends Collection<array-key, Alert>
 */
class AlertCollection extends Collection
{
    public function sortedByLevel(AlertTypes $alertTypes): self
    {
        return $this
            ->sortBy(static function (Alert $alert) use ($alertTypes): int {
                return $alertTypes->alertType($alert->alert_type_identifier)
                    ->level()
                    ->order();
            })
            ->values();
    }
}
