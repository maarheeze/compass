<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

use function ltrim;
use function rtrim;
use function sprintf;

class CheckSiteEndpoint implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly SiteEndpoint $siteEndpoint,
    ) {
    }

    public function handle(CollectorLogger $collectorLogger, Context $context, HttpFactory $http): void
    {
        $site = $this->siteEndpoint->site;
        $url = sprintf('%s/%s', rtrim($site->url, '/'), ltrim($this->siteEndpoint->path, '/'));

        $context->add('site', $site->name);
        $context->add('path', $this->siteEndpoint->path);

        $siteEndpointCheck = SiteEndpointCheck::query()->create([
            'site_id' => $site->id,
            'path' => $this->siteEndpoint->path,
            'url' => $url,
            'status_code' => $this->findStatusCode($http, $url),
        ]);

        $collectorLogger->debug('Endpoint checked', ['status_code' => $siteEndpointCheck->status_code]);
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
