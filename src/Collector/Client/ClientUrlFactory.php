<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Maarheeze\Compass\Models\SiteReport;

use function ltrim;
use function rtrim;
use function sprintf;

readonly class ClientUrlFactory
{
    public function __construct(
        private string $defaultPath,
    ) {
    }

    public function create(SiteReport $siteReport): string
    {
        return sprintf(
            '%s/%s',
            rtrim($siteReport->site->url, '/'),
            ltrim($this->path($siteReport), '/'),
        );
    }

    public function path(SiteReport $siteReport): string
    {
        if ($siteReport->path === null) {
            return $this->defaultPath;
        }

        return $siteReport->path;
    }
}
