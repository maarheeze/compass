<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

use function array_key_exists;
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
    public function report(Site $site): SiteReport
    {
        $reportResponse = $this->getReportResponse($this->get($site));

        return new SiteReport(
            Assert::boolean($reportResponse['app_debug']),
            Assert::string($reportResponse['app_env']),
            Assert::string($reportResponse['app_name']),
            $this->getEndpoints(Assert::isList($reportResponse['endpoints'])),
        );
    }

    /**
     * @throws ClientUnreachable
     */
    private function get(Site $site): Response
    {
        try {
            return $this->http
                ->withToken($this->clientKey->forSite($site))
                ->acceptJson()
                ->timeout(10)
                ->get($this->clientUrlFactory->create($site));
        } catch (ConnectionException $exception) {
            throw new ClientUnreachable(previous: $exception);
        }
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

        if (!is_array($reportResponse) || !array_key_exists('endpoints', $reportResponse)) {
            throw new ClientNotInstalled();
        }

        return $reportResponse;
    }
}
