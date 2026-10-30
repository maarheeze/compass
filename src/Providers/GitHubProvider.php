<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQuery;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Console\SyncGitHubCommand;
use Maarheeze\Compass\View\Components\IssueReference;

class GitHubProvider extends ServiceProvider
{
    public function boot(Repository $config): void
    {
        $this->commands([SyncGitHubCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule) use ($config): void {
            $schedule->command(SyncGitHubCommand::class)
                ->hourly()
                ->when(static function () use ($config): bool {
                    return $config->boolean('compass.schedule.github');
                });
        });
    }

    public function register(): void
    {
        $this->app
            ->when(GitHubClient::class)
            ->needs('$token')
            ->giveConfig('compass.github.token');

        $this->app
            ->when([BlobQuery::class, IssueReference::class, IssuesAndPullRequestsQuery::class, RepositoriesQuery::class])
            ->needs('$organization')
            ->giveConfig('compass.github.organization');
    }
}
