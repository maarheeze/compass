<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Collector\CollectorSchedule;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\SiteReport;

readonly class SiteClient
{
    public function __construct(
        private CollectorSchedule $collectorSchedule,
        private HttpFactory $http,
        private string $path,
        private string $source,
        private UrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @throws ClientUnreachable
     */
    public function post(SiteReport $siteReport, string $key): Response
    {
        try {
            return $this->http
                ->baseUrl($siteReport->site->url)
                ->withToken($key)
                ->acceptJson()
                ->timeout(10)
                ->post($this->reportPath($siteReport), $this->compassApiFields());
        } catch (ConnectionException $exception) {
            throw new ClientUnreachable(previous: $exception);
        }
    }

    /**
     * @return array<string, string>
     */
    private function compassApiFields(): array
    {
        $siteReportsNextRunAt = $this->collectorSchedule->nextRunAt($this->source, CollectorType::SITE_REPORTS);

        if ($siteReportsNextRunAt === null) {
            return [];
        }

        return [
            'compass_api_url' => $this->urlGenerator->route(RouteName::API_CLIENT->value),
            'compass_api_url_valid_until' => $siteReportsNextRunAt->addHour()->toIso8601String(),
        ];
    }

    private function reportPath(SiteReport $siteReport): string
    {
        if ($siteReport->path === null) {
            return $this->path;
        }

        return $siteReport->path;
    }
}
