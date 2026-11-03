<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\Issues\CollectedIssue;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

readonly class IssueSynchronizer
{
    public function deleteUncollected(Uuid $runId): void
    {
        Issue::query()
            ->notReturnedByRun($runId)
            ->delete();
    }

    public function storeIssues(CollectedIssueCollection $collectedIssues, Uuid $runId): void
    {
        $repositoryIds = Repository::query()
            ->named($collectedIssues->repositoryNames())
            ->pluck('id', 'name');
        $collectedIssuesOfKnownRepositories = $collectedIssues->filter(
            static function (CollectedIssue $collectedIssue) use ($repositoryIds): bool {
                return $repositoryIds->has($collectedIssue->repositoryName);
            },
        );
        $issueValues = [];

        foreach ($collectedIssuesOfKnownRepositories as $collectedIssue) {
            $issueValues[] = [
                'repository_id' => $repositoryIds->get($collectedIssue->repositoryName),
                'github_id' => $collectedIssue->externalId,
                'number' => $collectedIssue->number,
                'title' => $collectedIssue->title,
                'url' => $collectedIssue->url,
                'author_login' => $collectedIssue->authorLogin,
                'opened_at' => $collectedIssue->openedAt,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
            ];
        }

        Issue::query()->upsert($issueValues, ['github_id']);
        $this->storeAssignees($collectedIssuesOfKnownRepositories);
    }

    private function storeAssignees(CollectedIssueCollection $collectedIssues): void
    {
        $issuesOfCollectedIssues = Issue::query()
            ->ofGitHubIds($collectedIssues->externalIds())
            ->get();
        $issueIds = $issuesOfCollectedIssues->pluck('id', 'github_id');
        $assigneeValues = [];

        foreach ($collectedIssues as $collectedIssue) {
            foreach ($collectedIssue->assigneeLogins as $assigneeLogin) {
                $assigneeValues[] = [
                    'id' => Uuid::generate(),
                    'issue_id' => $issueIds->get($collectedIssue->externalId),
                    'login' => $assigneeLogin,
                    'created_at' => CarbonImmutable::now(),
                    'updated_at' => CarbonImmutable::now(),
                ];
            }
        }

        IssueAssignee::query()
            ->ofIssues($issuesOfCollectedIssues)
            ->delete();
        IssueAssignee::query()->insert($assigneeValues);
    }
}
