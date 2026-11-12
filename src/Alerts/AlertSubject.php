<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Builders\AlertBuilder;

interface AlertSubject
{
    /**
     * @return MorphMany<AlertCheck, covariant Model>
     */
    public function alertChecks(): MorphMany;

    /**
     * @return MorphMany<Alert, covariant Model>
     */
    public function alerts(): MorphMany;

    public function alertsIncludingChildren(): AlertBuilder;
}
