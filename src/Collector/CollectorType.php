<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Uuid\Uuid;

enum CollectorType: string
{
    case ISSUES = 'issues';
    case PULL_REQUESTS = 'pull_requests';
    case REPOSITORIES = 'repositories';
    case SITE_REPORTS = 'site_reports';
    case SITES = 'sites';

    public function job(Uuid $runId, string $sourceKey): ShouldQueue
    {
        return match ($this) {
            self::ISSUES => new CollectIssues($runId, $sourceKey),
            self::PULL_REQUESTS => new CollectPullRequests($runId, $sourceKey),
            self::REPOSITORIES => new CollectRepositories($runId, $sourceKey),
            self::SITE_REPORTS => new InspectSites($sourceKey),
            self::SITES => new CollectSites($runId, $sourceKey),
        };
    }
}
