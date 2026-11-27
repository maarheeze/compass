<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Compass\Collector\Issues\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\PullRequests\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\Repositories\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\SiteProcesses\Jobs\CollectSiteProcessesOfSites;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSites;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Compass\View\LabelledEnum;
use Maarheeze\Uuid\Uuid;

use function sprintf;

enum CollectorType: string implements LabelledEnum
{
    case ISSUES = 'issues';
    case PULL_REQUESTS = 'pull_requests';
    case REPOSITORIES = 'repositories';
    case SITE_PROCESSES = 'site_processes';
    case SITE_REPORTS = 'site_reports';
    case SITES = 'sites';

    /**
     * @return list<self>
     */
    public static function displayOrder(): array
    {
        return [
            self::REPOSITORIES,
            self::ISSUES,
            self::PULL_REQUESTS,
            self::SITES,
            self::SITE_PROCESSES,
            self::SITE_REPORTS,
        ];
    }

    public function job(Uuid $runId, string $source): LockedJob
    {
        return match ($this) {
            self::ISSUES => new CollectIssues($runId, $source),
            self::PULL_REQUESTS => new CollectPullRequests($runId, $source),
            self::REPOSITORIES => new CollectRepositories($runId, $source),
            self::SITE_PROCESSES => new CollectSiteProcessesOfSites($source),
            self::SITE_REPORTS => new InspectSites($source),
            self::SITES => new CollectSites($runId, $source),
        };
    }

    public function labelKey(): string
    {
        return sprintf('compass::collector-type.%s', $this->name);
    }

    public function scheduledPriority(): QueuePriority
    {
        return match ($this) {
            self::REPOSITORIES, self::SITE_PROCESSES => QueuePriority::LOW,
            self::ISSUES, self::PULL_REQUESTS, self::SITE_REPORTS, self::SITES => QueuePriority::MEDIUM,
        };
    }
}
