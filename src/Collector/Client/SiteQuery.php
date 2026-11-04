<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use Maarheeze\Compass\Models\SiteReport;
use Webmozart\Assert\Assert;

use function is_array;

readonly class SiteQuery
{
    public function __construct(
        private ClientKey $clientKey,
        private ClientUrlFactory $clientUrlFactory,
        private HttpFactory $http,
    ) {
    }

    /**
     * @throws ClientRequestFailed
     */
    public function report(SiteReport $siteReport): ClientReport
    {
        $reportResponse = $this->getReportResponse($this->get($siteReport));

        try {
            return $this->getClientReport($reportResponse);
        } catch (InvalidArgumentException $exception) {
            throw new ClientIncompatible(previous: $exception);
        }
    }

    /**
     * @throws ClientUnreachable
     */
    private function get(SiteReport $siteReport): Response
    {
        try {
            return $this->http
                ->withToken($this->clientKey->forSiteReport($siteReport))
                ->acceptJson()
                ->timeout(10)
                ->get($this->clientUrlFactory->create($siteReport));
        } catch (ConnectionException $exception) {
            throw new ClientUnreachable(previous: $exception);
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
     * @param list<mixed> $endpointsResponse
     *
     * @return list<string>
     */
    private function getEndpoints(array $endpointsResponse): array
    {
        $endpoints = [];

        foreach ($endpointsResponse as $endpointResponse) {
            $endpoints[] = Assert::string($endpointResponse);
        }

        return $endpoints;
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
            throw new ClientUnauthorized();
        }

        if (!$response->ok()) {
            throw new ClientUnreachable();
        }

        $reportResponse = $response->json();

        if (!is_array($reportResponse)) {
            throw new ClientNotInstalled();
        }

        return $reportResponse;
    }
}
