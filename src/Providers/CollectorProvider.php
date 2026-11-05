<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollectorManager;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollectorManager;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;
use Maarheeze\Compass\Collector\SourceConfigs;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\CollectCommand;

class CollectorProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([CheckSiteEndpointsCommand::class, CollectCommand::class]);
    }

    public function register(): void
    {
        $this->app->singleton(ComposerFilesCollectorManager::class);
        $this->app->singleton(DependabotFilesCollectorManager::class);
        $this->app->singleton(IssuesCollectorManager::class);
        $this->app->singleton(PullRequestsCollectorManager::class);
        $this->app->singleton(RepositoriesCollectorManager::class);
        $this->app->singleton(SiteReportsCollectorManager::class);
        $this->app->singleton(SitesCollectorManager::class);

        $this->app
            ->when(SourceConfigs::class)
            ->needs('$sources')
            ->giveConfig('compass.sources');
    }
}
