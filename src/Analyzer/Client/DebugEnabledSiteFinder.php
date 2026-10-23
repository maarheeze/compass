<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Client;

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class DebugEnabledSiteFinder
{
    public function findSites(): SiteCollection
    {
        return Assert::isInstanceOf(
            Site::query()
                ->whereNull('missing_at')
                ->where('app_debug', true)
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );
    }
}
