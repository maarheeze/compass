<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

use function ltrim;
use function rtrim;
use function sprintf;

class CheckSiteEndpoint implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly SiteEndpoint $siteEndpoint,
    ) {
    }

    public function handle(HttpFactory $http): void
    {
        $site = $this->siteEndpoint->site;
        $url = sprintf('%s/%s', rtrim($site->url, '/'), ltrim($this->siteEndpoint->path, '/'));

        SiteEndpointCheck::query()->create([
            'site_id' => $site->id,
            'path' => $this->siteEndpoint->path,
            'url' => $url,
            'status_code' => $this->findStatusCode($http, $url),
        ]);
    }

    private function findStatusCode(HttpFactory $http, string $url): ?int
    {
        try {
            return $http
                ->withoutRedirecting()
                ->timeout(10)
                ->get($url)
                ->status();
        } catch (ConnectionException) {
            return null;
        }
    }
}
