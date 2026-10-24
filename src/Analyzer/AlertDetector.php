<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Collections\AlertCollection;

interface AlertDetector
{
    public function findAlerts(): AlertCollection;
}
