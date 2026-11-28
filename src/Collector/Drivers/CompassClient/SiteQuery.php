<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Response as HttpResponse;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduledTask;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduler;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function is_array;

readonly class SiteQuery
{
    private const array REPORT_FIELD_NAMES = [
        'app_debug',
        'app_env',
        'app_name',
        'endpoints',
        'maintenance_mode',
        'queue_driver',
        'scheduler',
    ];

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
        Assert::isMap($reportResponse);
        Assert::true($this->hasReportField($reportResponse));

        return new ClientReport(
            $this->getOptionalBoolean($reportResponse, 'app_debug'),
            $this->getOptionalString($reportResponse, 'app_env'),
            $this->getOptionalString($reportResponse, 'app_name'),
            $this->getEndpoints($reportResponse),
            $this->getOptionalBoolean($reportResponse, 'maintenance_mode'),
            $this->getOptionalString($reportResponse, 'queue_driver'),
            $this->getScheduler($reportResponse),
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
     * @param array<mixed> $reportResponse
     *
     * @return list<CollectedSiteEndpoint>|null
     */
    private function getEndpoints(array $reportResponse): ?array
    {
        if (!array_key_exists('endpoints', $reportResponse)) {
            return null;
        }

        $collectedSiteEndpoints = [];

        foreach (Assert::isList($reportResponse['endpoints']) as $endpointResponse) {
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

    /**
     * @param array<mixed> $reportResponse
     */
    private function getScheduler(array $reportResponse): ?CollectedScheduler
    {
        if (!array_key_exists('scheduler', $reportResponse)) {
            return null;
        }

        $schedulerResponse = Assert::isMap($reportResponse['scheduler']);
        Assert::keyExists($schedulerResponse, 'last_ran_at');
        Assert::keyExists($schedulerResponse, 'tasks');

        return new CollectedScheduler(
            $this->getSchedulerLastRanAt(Assert::nullOrString($schedulerResponse['last_ran_at'])),
            $this->getScheduledTasks(Assert::isList($schedulerResponse['tasks'])),
        );
    }

    private function getSchedulerLastRanAt(?string $lastRanAt): ?CarbonImmutable
    {
        if ($lastRanAt === null) {
            return null;
        }

        return CarbonImmutable::parse($lastRanAt);
    }

    /**
     * @param array<mixed> $reportResponse
     */
    private function hasReportField(array $reportResponse): bool
    {
        foreach (self::REPORT_FIELD_NAMES as $reportFieldName) {
            if (array_key_exists($reportFieldName, $reportResponse)) {
                return true;
            }
        }

        return false;
    }
}
