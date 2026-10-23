<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Client;

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class UnauthorizedClientSiteFinder
{
    public function findSites(): SiteCollection
    {
        return Assert::isInstanceOf(
            Site::query()
                ->where('client_status', ClientStatus::UNAUTHORIZED)
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );
    }
}
