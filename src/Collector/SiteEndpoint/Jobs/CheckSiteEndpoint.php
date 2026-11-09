<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

use function ltrim;
use function rtrim;
use function sprintf;

class CheckSiteEndpoint implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly SiteEndpoint $siteEndpoint,
    ) {
    }

    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        Dispatcher $events,
        HttpFactory $http,
    ): void {
        $site = $this->siteEndpoint->siteReport->site;
        $url = sprintf('%s/%s', rtrim($site->url, '/'), ltrim($this->siteEndpoint->path, '/'));

        $context->add('site', $site->name);
        $context->add('path', $this->siteEndpoint->path);

        $response = $this->getResponse($http, $url);

        $siteEndpointCheck = SiteEndpointCheck::query()->create([
            'site_id' => $site->id,
            'path' => new EndpointPath($this->siteEndpoint->path),
            'url' => $url,
            'status_code' => $response?->status(),
            'is_successful' => $this->isSuccesful($response),
        ]);

        $collectorLogger->debug('Endpoint checked', [
            'status_code' => $siteEndpointCheck->status_code,
            'is_successful' => $siteEndpointCheck->is_successful,
        ]);

        $events->dispatch(new SiteEndpointChecked($this->siteEndpoint, $siteEndpointCheck));
    }

    public function lockKey(): string
    {
        return $this->siteEndpoint->id->toString();
    }

    private function getResponse(HttpFactory $http, string $url): ?Response
    {
        try {
            return $http
                ->withoutRedirecting()
                ->timeout(10)
                ->get($url);
        } catch (ConnectionException) {
            return null;
        }
    }

    private function isSuccesful(?Response $response): bool
    {
        if ($response === null) {
            return false;
        }

        return $response->successful();
    }
}
