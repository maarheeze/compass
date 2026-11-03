<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Events\IssuesRunSucceeded;
use Maarheeze\Compass\Collector\Events\PullRequestsRunSucceeded;
use Maarheeze\Compass\Collector\Events\RunSucceeded;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Uuid\Uuid;

readonly class CollectionStarter
{
    public function __construct(
        private RunRecorder $runRecorder,
    ) {
    }

    public function start(Source $source, CollectionType $collectionType): void
    {
        $runId = Uuid::generate();

        $this->runRecorder->start(
            $runId,
            $source,
            $collectionType,
            $this->firstJob($collectionType, $runId),
            $this->runSucceeded($collectionType, $runId),
        );
    }

    private function firstJob(CollectionType $collectionType, Uuid $runId): ShouldQueue
    {
        return match ($collectionType) {
            CollectionType::ISSUES => new CollectIssues($runId),
            CollectionType::PULL_REQUESTS => new CollectPullRequests($runId),
            CollectionType::REPOSITORIES => new CollectRepositories($runId),
            CollectionType::SITES => new CollectSites($runId),
        };
    }

    private function runSucceeded(CollectionType $collectionType, Uuid $runId): RunSucceeded
    {
        return match ($collectionType) {
            CollectionType::ISSUES => new IssuesRunSucceeded($runId),
            CollectionType::PULL_REQUESTS => new PullRequestsRunSucceeded($runId),
            CollectionType::REPOSITORIES => new GitHubRunSucceeded($runId),
            CollectionType::SITES => new ForgeRunSucceeded($runId),
        };
    }
}
