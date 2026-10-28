<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Models\Alert;

/**
 * @extends Builder<Alert>
 */
class AlertBuilder extends Builder
{
    public function notDone(): self
    {
        return $this->whereNot('status', AlertStatus::DONE);
    }
}
