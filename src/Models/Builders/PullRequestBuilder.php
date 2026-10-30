<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\User;
use Maarheeze\Uuid\Uuid;

/**
 * @extends Builder<PullRequest>
 */
class PullRequestBuilder extends Builder
{
    public function assignedTo(User $user): self
    {
        return $this->whereHas('assignees', static function (Builder $assigneeQuery) use ($user): void {
            $assigneeQuery->where('login', $user->nickname);
        });
    }

    public function notReturnedByRun(Uuid $runId): self
    {
        return $this->whereNot('run_id', $runId);
    }
}
