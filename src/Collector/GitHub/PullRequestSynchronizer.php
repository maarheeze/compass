<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

readonly class PullRequestSynchronizer
{
    public function deleteUncollected(string $source, Uuid $runId): void
    {
        PullRequest::query()
            ->ofSource($source)
            ->notReturnedByRun($runId)
            ->delete();
    }

    public function storePullRequests(
        CollectedPullRequestCollection $collectedPullRequests,
        string $source,
        Uuid $runId,
    ): void {
        $repositoryIds = Repository::query()
            ->ofSource($source)
            ->ofFullNames($collectedPullRequests->repositoryLookupKeys())
            ->pluck('id', 'full_name');
        $pullRequestValues = [];

        foreach ($collectedPullRequests as $collectedPullRequest) {
            $pullRequestValues[] = [
                'repository_id' => $repositoryIds->get($collectedPullRequest->repositoryLookupKey),
                'source' => $source,
                'external_id' => $collectedPullRequest->externalId,
                'repository_lookup_key' => $collectedPullRequest->repositoryLookupKey,
                'number' => $collectedPullRequest->number,
                'title' => $collectedPullRequest->title,
                'url' => $collectedPullRequest->url,
                'author_login' => $collectedPullRequest->authorLogin,
                'is_draft' => $collectedPullRequest->isDraft,
                'opened_at' => $collectedPullRequest->openedAt,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
            ];
        }

        PullRequest::query()->upsert($pullRequestValues, ['source', 'external_id']);
        $this->storeAssignees($collectedPullRequests, $source);
    }

    private function storeAssignees(CollectedPullRequestCollection $collectedPullRequests, string $source): void
    {
        $pullRequestsOfCollectedPullRequests = PullRequest::query()
            ->ofSource($source)
            ->ofExternalIds($collectedPullRequests->externalIds())
            ->get();
        $pullRequestIds = $pullRequestsOfCollectedPullRequests->pluck('id', 'external_id');
        $assigneeValues = [];

        foreach ($collectedPullRequests as $collectedPullRequest) {
            foreach ($collectedPullRequest->assigneeLogins as $assigneeLogin) {
                $assigneeValues[] = [
                    'id' => Uuid::generate(),
                    'pull_request_id' => $pullRequestIds->get($collectedPullRequest->externalId),
                    'login' => $assigneeLogin,
                    'created_at' => CarbonImmutable::now(),
                    'updated_at' => CarbonImmutable::now(),
                ];
            }
        }

        PullRequestAssignee::query()
            ->ofPullRequests($pullRequestsOfCollectedPullRequests)
            ->delete();
        PullRequestAssignee::query()->insert($assigneeValues);
    }
}
