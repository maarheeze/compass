<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class MissingSiteFinder
{
    public function findSites(): SiteCollection
    {
        return Assert::isInstanceOf(
            Site::query()
                ->whereNotNull('missing_at')
                ->whereNull('missing_ignored_at')
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );
    }
}
