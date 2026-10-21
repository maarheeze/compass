<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Console\SyncGitHubCommand;

class CollectorProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([SyncForgeCommand::class, SyncGitHubCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command(SyncForgeCommand::class)
                ->hourly();
            $schedule->command(SyncGitHubCommand::class)
                ->hourly();
        });
    }

    public function register(): void
    {
        $this->app
            ->when(ForgeClient::class)
            ->needs('$token')
            ->giveConfig('compass.collector.forge_token');

        $this->app
            ->when(SitesQuery::class)
            ->needs('$organization')
            ->giveConfig('compass.forge_organization');

        $this->app
            ->when(GitHubClient::class)
            ->needs('$token')
            ->giveConfig('compass.collector.github_token');

        $this->app
            ->when([BlobQuery::class, RepositoriesQuery::class])
            ->needs('$organization')
            ->giveConfig('compass.github_organization');
    }
}
