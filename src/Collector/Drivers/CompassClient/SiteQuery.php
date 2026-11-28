<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Response as HttpResponse;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduledTask;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Webmozart\Assert\Assert;

use function array_key_exists;
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
        $reportResponse = $this->getReportResponse($this->siteClient->post($siteReport, $key));

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
        Assert::keyExists($reportResponse, 'endpoints');
        Assert::keyExists($reportResponse, 'scheduler');

        $schedulerResponse = Assert::isMap($reportResponse['scheduler']);
        Assert::keyExists($schedulerResponse, 'last_ran_at');
        Assert::keyExists($schedulerResponse, 'tasks');

        return new ClientReport(
            $this->getOptionalBoolean($reportResponse, 'app_debug'),
            $this->getOptionalString($reportResponse, 'app_env'),
            $this->getOptionalString($reportResponse, 'app_name'),
            $this->getEndpoints(Assert::isList($reportResponse['endpoints'])),
            $this->getOptionalBoolean($reportResponse, 'maintenance_mode'),
            $this->getOptionalString($reportResponse, 'queue_driver'),
            $this->getScheduledTasks(Assert::isList($schedulerResponse['tasks'])),
            $this->getSchedulerLastRanAt(Assert::nullOrString($schedulerResponse['last_ran_at'])),
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
     * @param array<mixed> $reportResponse
     */
    private function getOptionalBoolean(array $reportResponse, string $fieldName): ?bool
    {
        if (!array_key_exists($fieldName, $reportResponse)) {
            return null;
        }

        return Assert::boolean($reportResponse[$fieldName]);
    }

    /**
     * @param array<mixed> $reportResponse
     */
    private function getOptionalString(array $reportResponse, string $fieldName): ?string
    {
        if (!array_key_exists($fieldName, $reportResponse)) {
            return null;
        }

        return Assert::string($reportResponse[$fieldName]);
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

        if ($response->status() === HttpResponse::HTTP_METHOD_NOT_ALLOWED) {
            throw new ClientIncompatible();
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

    /**
     * @param array<string, mixed> $scheduledTaskResponse
     */
    private function getScheduledTask(array $scheduledTaskResponse): CollectedScheduledTask
    {
        Assert::keyExists($scheduledTaskResponse, 'command');
        Assert::keyExists($scheduledTaskResponse, 'expression');

        return new CollectedScheduledTask(
            Assert::string($scheduledTaskResponse['command']),
            Assert::string($scheduledTaskResponse['expression']),
        );
    }

    /**
     * @param list<mixed> $scheduledTasksResponse
     *
     * @return list<CollectedScheduledTask>
     */
    private function getScheduledTasks(array $scheduledTasksResponse): array
    {
        $collectedScheduledTasks = [];

        foreach ($scheduledTasksResponse as $scheduledTaskResponse) {
            $collectedScheduledTasks[] = $this->getScheduledTask(Assert::isMap($scheduledTaskResponse));
        }

        return $collectedScheduledTasks;
    }

    private function getSchedulerLastRanAt(?string $lastRanAt): ?CarbonImmutable
    {
        if ($lastRanAt === null) {
            return null;
        }

        return CarbonImmutable::parse($lastRanAt);
    }
}
