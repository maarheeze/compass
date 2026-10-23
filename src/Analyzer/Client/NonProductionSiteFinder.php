<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Client;

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class NonProductionSiteFinder
{
    private const string PRODUCTION_ENVIRONMENT = 'production';

    public function findSites(): SiteCollection
    {
        return Assert::isInstanceOf(
            Site::query()
                ->whereNull('missing_at')
                ->whereNotNull('app_env')
                ->where('app_env', '!=', self::PRODUCTION_ENVIRONMENT)
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );
    }
}
