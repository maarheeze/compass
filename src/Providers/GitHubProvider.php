<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Console\SyncGitHubCommand;

class GitHubProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([SyncGitHubCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command(SyncGitHubCommand::class)
                ->hourly();
        });
    }

    public function register(): void
    {
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
