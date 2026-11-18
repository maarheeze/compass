<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Webmozart\Assert\Assert;

use function is_array;

readonly class SiteQuery
{
    public function __construct(
        private SiteClient $siteClient,
    ) {
    }

    /**
     * @throws ClientRequestFailed
     */
    public function report(SiteReport $siteReport, string $key): ClientReport
    {
        $reportResponse = $this->getReportResponse($this->siteClient->get($siteReport, $key));

        try {
            return $this->getClientReport($reportResponse);
        } catch (InvalidArgumentException $exception) {
            throw new ClientIncompatible(previous: $exception);
        }
    }

    /**
     * @param array<mixed> $reportResponse
     */
    private function getClientReport(array $reportResponse): ClientReport
    {
        Assert::keyExists($reportResponse, 'app_debug');
        Assert::keyExists($reportResponse, 'app_env');
        Assert::keyExists($reportResponse, 'app_name');
        Assert::keyExists($reportResponse, 'endpoints');

        return new ClientReport(
            Assert::boolean($reportResponse['app_debug']),
            Assert::string($reportResponse['app_env']),
            Assert::string($reportResponse['app_name']),
            $this->getEndpoints(Assert::isList($reportResponse['endpoints'])),
        );
    }

    /**
     * @param array<string, mixed> $endpointResponse
     */
    private function getEndpoint(array $endpointResponse): CollectedSiteEndpoint
    {
        Assert::keyExists($endpointResponse, 'path');
        Assert::keyExists($endpointResponse, 'frequency_minutes');

        return new CollectedSiteEndpoint(
            new EndpointPath(Assert::string($endpointResponse['path'])),
            Assert::positiveInteger($endpointResponse['frequency_minutes']),
        );
    }

    /**
     * @param list<mixed> $endpointsResponse
     *
     * @return list<CollectedSiteEndpoint>
     */
    private function getEndpoints(array $endpointsResponse): array
    {
        $collectedSiteEndpoints = [];

        foreach ($endpointsResponse as $endpointResponse) {
            $collectedSiteEndpoints[] = $this->getEndpoint(Assert::isMap($endpointResponse));
        }

        return $collectedSiteEndpoints;
    }

    /**
     * @return array<mixed>
     *
     * @throws ClientRequestFailed
     */
    private function getReportResponse(Response $response): array
    {
        if ($response->notFound()) {
            throw new ClientNotInstalled();
        }

        if ($response->unauthorized() || $response->forbidden()) {
            throw new ClientUnauthorised();
        }

        if (!$response->ok()) {
            throw new ClientUnreachable(previous: $response->toException());
        }

        $reportResponse = $response->json();

        if (!is_array($reportResponse)) {
            throw new ClientNotInstalled();
        }

        return $reportResponse;
    }
}
