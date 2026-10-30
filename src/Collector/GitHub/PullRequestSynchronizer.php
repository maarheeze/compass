<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
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
        GitHubPullRequestCollection $gitHubPullRequests,
        Uuid $runId,
    ): void {
        $repositoryIds = Repository::query()
            ->whereIn('name', $gitHubPullRequests->pluck('repositoryName'))
            ->pluck('id', 'name');
        $gitHubPullRequestsOfKnownRepositories = $gitHubPullRequests->filter(
            static function (GitHubPullRequest $gitHubPullRequest) use ($repositoryIds): bool {
                return $repositoryIds->has($gitHubPullRequest->repositoryName);
            },
        );
        $pullRequestValues = [];

        foreach ($gitHubPullRequestsOfKnownRepositories as $gitHubPullRequest) {
            $pullRequestValues[] = [
                'repository_id' => $repositoryIds->get($gitHubPullRequest->repositoryName),
                'github_id' => $gitHubPullRequest->gitHubId,
                'number' => $gitHubPullRequest->number,
                'title' => $gitHubPullRequest->title,
                'url' => $gitHubPullRequest->url,
                'author_login' => $gitHubPullRequest->authorLogin,
                'is_draft' => $gitHubPullRequest->isDraft,
                'opened_at' => $gitHubPullRequest->openedAt,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
            ];
        }

        PullRequest::query()->upsert($pullRequestValues, ['github_id']);
        $this->storeAssignees($gitHubPullRequestsOfKnownRepositories);
    }

    private function storeAssignees(GitHubPullRequestCollection $gitHubPullRequests): void
    {
        $pullRequestIds = PullRequest::query()
            ->whereIn('github_id', $gitHubPullRequests->pluck('gitHubId'))
            ->pluck('id', 'github_id');
        $assigneeValues = [];

        foreach ($gitHubPullRequests as $gitHubPullRequest) {
            foreach ($gitHubPullRequest->assigneeLogins as $assigneeLogin) {
                $assigneeValues[] = [
                    'id' => Uuid::generate(),
                    'pull_request_id' => $pullRequestIds->get($gitHubPullRequest->gitHubId),
                    'login' => $assigneeLogin,
                    'created_at' => CarbonImmutable::now(),
                    'updated_at' => CarbonImmutable::now(),
                ];
            }
        }

        PullRequestAssignee::query()
            ->whereIn('pull_request_id', $pullRequestIds)
            ->delete();
        PullRequestAssignee::query()->insert($assigneeValues);
    }
}
