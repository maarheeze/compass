<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\User;
use Maarheeze\Uuid\Uuid;

/**
 * @extends Builder<Issue>
 */
class IssueBuilder extends Builder
{
    public function assignedTo(User $user): self
    {
        return $this->whereHas('assignees', static function (Builder $assigneeQuery) use ($user): void {
            $assigneeQuery->where('login', $user->nickname);
        });
    }

    public function latestOpened(): self
    {
        return $this->latest('opened_at');
    }

    public function notReturnedByRun(Uuid $runId): self
    {
        return $this->whereNot('run_id', $runId);
    }

    /**
     * @param Collection<int, string> $gitHubIds
     */

    public function ofGitHubIds(Collection $gitHubIds): self
    {
        return $this->whereIn('github_id', $gitHubIds);
    }
}
