<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class IssueSynchronizer
{
    public function deleteUncollected(CollectionRunId $collectionRunId): void
    {
        Issue::query()
            ->whereNot('collection_run_id', $collectionRunId)
            ->delete();
    }

    public function storeIssues(GitHubIssueCollection $gitHubIssues, CollectionRunId $collectionRunId): void
    {
        $repositoryIds = Repository::query()
            ->whereIn('name', $gitHubIssues->pluck('repositoryName'))
            ->pluck('id', 'name');
        $gitHubIssuesOfKnownRepositories = $gitHubIssues->filter(
            static function (GitHubIssue $gitHubIssue) use ($repositoryIds): bool {
                return $repositoryIds->has($gitHubIssue->repositoryName);
            },
        );
        $issueValues = [];

        foreach ($gitHubIssuesOfKnownRepositories as $gitHubIssue) {
            $issueValues[] = [
                'repository_id' => Assert::integer($repositoryIds->get($gitHubIssue->repositoryName)),
                'github_id' => $gitHubIssue->gitHubId,
                'number' => $gitHubIssue->number,
                'title' => $gitHubIssue->title,
                'url' => $gitHubIssue->url,
                'author_login' => $gitHubIssue->authorLogin,
                'opened_at' => $gitHubIssue->openedAt,
                'collection_run_id' => $collectionRunId,
                'collected_at' => CarbonImmutable::now(),
            ];
        }

        Issue::query()->upsert($issueValues, ['github_id']);
        $this->storeAssignees($gitHubIssuesOfKnownRepositories);
    }

    private function storeAssignees(GitHubIssueCollection $gitHubIssues): void
    {
        $issueIds = Issue::query()
            ->whereIn('github_id', $gitHubIssues->pluck('gitHubId'))
            ->pluck('id', 'github_id');
        $assigneeValues = [];

        foreach ($gitHubIssues as $gitHubIssue) {
            foreach ($gitHubIssue->assigneeLogins as $assigneeLogin) {
                $assigneeValues[] = [
                    'issue_id' => Assert::integer($issueIds->get($gitHubIssue->gitHubId)),
                    'login' => $assigneeLogin,
                    'created_at' => CarbonImmutable::now(),
                    'updated_at' => CarbonImmutable::now(),
                ];
            }
        }

        IssueAssignee::query()
            ->whereIn('issue_id', $issueIds)
            ->delete();
        IssueAssignee::query()->insert($assigneeValues);
    }
}
