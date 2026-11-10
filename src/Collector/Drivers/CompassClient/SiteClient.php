<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Models\SiteReport;

readonly class SiteClient
{
    public function __construct(
        private HttpFactory $http,
        private string $key,
        private string $path,
    ) {
    }

    /**
     * @throws ClientUnreachable
     */
    public function get(SiteReport $siteReport): Response
    {
        try {
            return $this->http
                ->baseUrl($siteReport->site->url)
                ->withToken($this->reportKey($siteReport))
                ->acceptJson()
                ->timeout(10)
                ->get($this->reportPath($siteReport));
        } catch (ConnectionException $exception) {
            throw new ClientUnreachable(previous: $exception);
        }
    }

    private function reportKey(SiteReport $siteReport): string
    {
        if ($siteReport->key === null) {
            return $this->key;
        }

        return $siteReport->key;
    }

    private function reportPath(SiteReport $siteReport): string
    {
        if ($siteReport->path === null) {
            return $this->path;
        }

        return $siteReport->path;
    }
}
