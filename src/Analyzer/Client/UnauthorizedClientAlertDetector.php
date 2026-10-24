<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Client;

use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class UnauthorizedClientAlertDetector implements AlertDetector
{
    public function findAlerts(): AlertCollection
    {
        $sites = Assert::isInstanceOf(
            Site::query()
                ->where('client_status', ClientStatus::UNAUTHORIZED)
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );

        return new AlertCollection(
            $sites
                ->toBase()
                ->mapInto(UnauthorizedClientAlert::class)
                ->values(),
        );
    }
}
