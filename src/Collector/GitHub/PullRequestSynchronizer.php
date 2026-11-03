<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\PullRequests\CollectedPullRequest;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

readonly class PullRequestSynchronizer
{
    public function deleteUncollected(Uuid $runId): void
    {
        PullRequest::query()
            ->notReturnedByRun($runId)
            ->delete();
    }

    public function storePullRequests(
        CollectedPullRequestCollection $collectedPullRequests,
        Uuid $runId,
    ): void {
        $repositoryIds = Repository::query()
            ->named($collectedPullRequests->repositoryNames())
            ->pluck('id', 'name');
        $collectedPullRequestsOfKnownRepositories = $collectedPullRequests->filter(
            static function (CollectedPullRequest $collectedPullRequest) use ($repositoryIds): bool {
                return $repositoryIds->has($collectedPullRequest->repositoryName);
            },
        );
        $pullRequestValues = [];

        foreach ($collectedPullRequestsOfKnownRepositories as $collectedPullRequest) {
            $pullRequestValues[] = [
                'repository_id' => $repositoryIds->get($collectedPullRequest->repositoryName),
                'github_id' => $collectedPullRequest->externalId,
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

        PullRequest::query()->upsert($pullRequestValues, ['github_id']);
        $this->storeAssignees($collectedPullRequestsOfKnownRepositories);
    }

    private function storeAssignees(CollectedPullRequestCollection $collectedPullRequests): void
    {
        $pullRequestsOfCollectedPullRequests = PullRequest::query()
            ->ofGitHubIds($collectedPullRequests->externalIds())
            ->get();
        $pullRequestIds = $pullRequestsOfCollectedPullRequests->pluck('id', 'github_id');
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
