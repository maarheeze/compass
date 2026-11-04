<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\CollectorRegistry;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollectorManager;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollectorManager;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\CollectCommand;

class CollectorProvider extends ServiceProvider
{
    public function boot(CollectorRegistry $collectorRegistry): void
    {
        $collectorRegistry->register(CollectorType::ISSUES, CollectIssues::class);
        $collectorRegistry->register(CollectorType::PULL_REQUESTS, CollectPullRequests::class);
        $collectorRegistry->register(CollectorType::REPOSITORIES, CollectRepositories::class);
        $collectorRegistry->register(CollectorType::SITE_REPORTS, InspectSites::class);
        $collectorRegistry->register(CollectorType::SITES, CollectSites::class);

        $this->commands([CheckSiteEndpointsCommand::class, CollectCommand::class]);
    }

    public function register(): void
    {
        $this->app->singleton(CollectorRegistry::class);
        $this->app->singleton(ComposerFilesCollectorManager::class);
        $this->app->singleton(DependabotFilesCollectorManager::class);
        $this->app->singleton(IssuesCollectorManager::class);
        $this->app->singleton(PullRequestsCollectorManager::class);
        $this->app->singleton(RepositoriesCollectorManager::class);
        $this->app->singleton(SiteReportsCollectorManager::class);
        $this->app->singleton(SitesCollectorManager::class);
    }
}
