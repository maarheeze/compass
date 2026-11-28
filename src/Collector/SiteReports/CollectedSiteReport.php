<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Carbon\CarbonImmutable;

readonly class CollectedSiteReport
{
    /**
     * @param list<CollectedSiteEndpoint>|null $endpoints
     * @param list<CollectedScheduledTask> $scheduledTasks
     */
    private function __construct(
        public SiteReportStatus $status,
        public ?bool $appDebug,
        public ?string $appEnv,
        public ?string $appName,
        public ?array $endpoints,
        public ?bool $maintenanceMode,
        public ?string $queueDriver,
        public array $scheduledTasks,
        public ?CarbonImmutable $schedulerLastRanAt,
    ) {
    }

    public static function incompatible(): self
    {
        return new self(SiteReportStatus::INCOMPATIBLE, null, null, null, [], null, null, [], null);
    }

    /**
     * @param list<CollectedSiteEndpoint>|null $endpoints
     * @param list<CollectedScheduledTask> $scheduledTasks
     */
    public static function installed(
        ?bool $appDebug,
        ?string $appEnv,
        ?string $appName,
        ?array $endpoints,
        ?bool $maintenanceMode,
        ?string $queueDriver,
        array $scheduledTasks,
        ?CarbonImmutable $schedulerLastRanAt,
    ): self {
        return new self(
            SiteReportStatus::INSTALLED,
            $appDebug,
            $appEnv,
            $appName,
            $endpoints,
            $maintenanceMode,
            $queueDriver,
            $scheduledTasks,
            $schedulerLastRanAt,
        );
    }

    public static function notInstalled(): self
    {
        return new self(SiteReportStatus::NOT_INSTALLED, null, null, null, [], null, null, [], null);
    }

    public static function unauthorised(): self
    {
        return new self(SiteReportStatus::UNAUTHORISED, null, null, null, [], null, null, [], null);
    }
}
