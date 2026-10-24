<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class MissingSiteAlertDetector implements AlertDetector
{
    public function findAlerts(): AlertCollection
    {
        $sites = Assert::isInstanceOf(
            Site::query()
                ->whereNotNull('missing_at')
                ->whereNull('missing_ignored_at')
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );

        return new AlertCollection(
            $sites
                ->toBase()
                ->mapInto(MissingSiteAlert::class)
                ->values(),
        );
    }
}
