<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

readonly class IssueSynchronizer
{
    public function deleteUncollected(string $source, Uuid $runId): void
    {
        Issue::query()
            ->ofSource($source)
            ->notReturnedByRun($runId)
            ->delete();
    }

    public function storeIssues(
        CollectedIssueCollection $collectedIssues,
        string $source,
        Uuid $runId,
    ): void {
        $repositoryIds = Repository::query()
            ->ofSource($source)
            ->ofFullNames($collectedIssues->repositoryLookupKeys())
            ->pluck('id', 'full_name');
        $issueValues = [];

        foreach ($collectedIssues as $collectedIssue) {
            $issueValues[] = [
                'repository_id' => $repositoryIds->get($collectedIssue->repositoryLookupKey),
                'source' => $source,
                'external_id' => $collectedIssue->externalId,
                'repository_lookup_key' => $collectedIssue->repositoryLookupKey,
                'number' => $collectedIssue->number,
                'title' => $collectedIssue->title,
                'url' => $collectedIssue->url,
                'author_login' => $collectedIssue->authorLogin,
                'opened_at' => $collectedIssue->openedAt,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
            ];
        }

        Issue::query()->upsert($issueValues, ['source', 'external_id']);
        $this->storeAssignees($collectedIssues, $source);
    }

    private function storeAssignees(CollectedIssueCollection $collectedIssues, string $source): void
    {
        $issuesOfCollectedIssues = Issue::query()
            ->ofSource($source)
            ->ofExternalIds($collectedIssues->externalIds())
            ->get();
        $issueIds = $issuesOfCollectedIssues->pluck('id', 'external_id');
        $assigneeValues = [];

        foreach ($collectedIssues as $collectedIssue) {
            foreach ($collectedIssue->assignees as $collectedAssignee) {
                $assigneeValues[] = [
                    'id' => Uuid::generate(),
                    'issue_id' => $issueIds->get($collectedIssue->externalId),
                    'login' => $collectedAssignee->login,
                    'avatar_url' => $collectedAssignee->avatarUrl,
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
