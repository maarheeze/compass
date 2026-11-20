<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

readonly class CollectedSiteReport
{
    /**
     * @param list<CollectedSiteEndpoint> $endpoints
     */
    private function __construct(
        public SiteReportStatus $status,
        public ?bool $appDebug,
        public ?string $appEnv,
        public ?string $appName,
        public array $endpoints,
        public ?bool $maintenanceMode,
    ) {
    }

    public static function incompatible(): self
    {
        return new self(SiteReportStatus::INCOMPATIBLE, null, null, null, [], null);
    }

    /**
     * @param list<CollectedSiteEndpoint> $endpoints
     */
    public static function installed(
        bool $appDebug,
        string $appEnv,
        string $appName,
        array $endpoints,
        bool $maintenanceMode,
    ): self {
        return new self(SiteReportStatus::INSTALLED, $appDebug, $appEnv, $appName, $endpoints, $maintenanceMode);
    }

    public static function notInstalled(): self
    {
        return new self(SiteReportStatus::NOT_INSTALLED, null, null, null, [], null);
    }

    public static function unauthorised(): self
    {
        return new self(SiteReportStatus::UNAUTHORISED, null, null, null, [], null);
    }
}
