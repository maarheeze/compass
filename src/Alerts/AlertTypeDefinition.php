<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\Models\Alert;

interface AlertTypeDefinition
{
    public function description(Alert $alert): string;
}
