<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollectorManager;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollectorManager;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;

arch('only the collector managers build the drivers')
    ->expect('Maarheeze\Compass\Collector\Drivers')
    ->toOnlyBeUsedIn([
        'Maarheeze\Compass\Collector\Drivers',
        ComposerFilesCollectorManager::class,
        DependabotFilesCollectorManager::class,
        IssuesCollectorManager::class,
        PullRequestsCollectorManager::class,
        RepositoriesCollectorManager::class,
        SiteReportsCollectorManager::class,
        SitesCollectorManager::class,
    ]);
