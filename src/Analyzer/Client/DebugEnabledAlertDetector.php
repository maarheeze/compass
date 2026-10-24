<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Client;

use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class DebugEnabledAlertDetector implements AlertDetector
{
    public function findAlerts(): AlertCollection
    {
        $sites = Assert::isInstanceOf(
            Site::query()
                ->whereNull('missing_at')
                ->where('app_debug', true)
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );

        return new AlertCollection(
            $sites
                ->toBase()
                ->mapInto(DebugEnabledAlert::class)
                ->values(),
        );
    }
}
