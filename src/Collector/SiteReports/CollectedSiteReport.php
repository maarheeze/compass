<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

readonly class CollectedSiteReport
{
    /**
     * @param list<string> $endpoints
     */
    private function __construct(
        public SiteReportStatus $status,
        public ?bool $appDebug,
        public ?string $appEnv,
        public ?string $appName,
        public array $endpoints,
    ) {
    }

    public static function incompatible(): self
    {
        return new self(SiteReportStatus::INCOMPATIBLE, null, null, null, []);
    }

    /**
     * @param list<string> $endpoints
     */
    public static function installed(bool $appDebug, string $appEnv, string $appName, array $endpoints): self
    {
        return new self(SiteReportStatus::INSTALLED, $appDebug, $appEnv, $appName, $endpoints);
    }

    public static function notInstalled(): self
    {
        return new self(SiteReportStatus::NOT_INSTALLED, null, null, null, []);
    }

    public static function unauthorized(): self
    {
        return new self(SiteReportStatus::UNAUTHORIZED, null, null, null, []);
    }
}
