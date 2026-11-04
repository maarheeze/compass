<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Maarheeze\Compass\Models\SiteReport;

readonly class ClientKey
{
    public function __construct(
        private string $defaultKey,
    ) {
    }

    public function forSiteReport(SiteReport $siteReport): string
    {
        if ($siteReport->key === null || $siteReport->key === '') {
            return $this->defaultKey;
        }

        return $siteReport->key;
    }
}
