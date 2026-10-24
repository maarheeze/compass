<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\AlertDetectorCollection;

readonly class AlertFinder
{
    public function __construct(
        private AlertDetectorCollection $alertDetectors,
    ) {
    }

    public function find(): AlertCollection
    {
        $alerts = new AlertCollection();

        foreach ($this->alertDetectors as $alertDetector) {
            $alerts = $alerts->merge($alertDetector->findAlerts());
        }

        return $alerts;
    }
}
