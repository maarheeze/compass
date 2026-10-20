<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\GitHubClient;
use Maarheeze\Compass\Collector\RepositoryCollector;

class CollectorProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app
            ->when(GitHubClient::class)
            ->needs('$token')
            ->giveConfig('compass.collector.github_token');

        $this->app
            ->when(RepositoryCollector::class)
            ->needs('$organization')
            ->giveConfig('compass.organization');
    }
}
