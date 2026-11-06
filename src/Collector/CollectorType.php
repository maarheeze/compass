<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\Issues\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\PullRequests\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\Repositories\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSites;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Uuid\Uuid;

enum CollectorType: string
{
    case ISSUES = 'issues';
    case PULL_REQUESTS = 'pull_requests';
    case REPOSITORIES = 'repositories';
    case SITE_REPORTS = 'site_reports';
    case SITES = 'sites';

    public function job(Uuid $runId, string $source): ShouldQueue
    {
        return match ($this) {
            self::ISSUES => new CollectIssues($runId, $source),
            self::PULL_REQUESTS => new CollectPullRequests($runId, $source),
            self::REPOSITORIES => new CollectRepositories($runId, $source),
            self::SITE_REPORTS => new InspectSites($source),
            self::SITES => new CollectSites($runId, $source),
        };
    }
}
