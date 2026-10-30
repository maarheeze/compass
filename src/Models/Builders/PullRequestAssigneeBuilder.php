<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Models\PullRequestAssignee;

/**
 * @extends Builder<PullRequestAssignee>
 */
class PullRequestAssigneeBuilder extends Builder
{
    public function ofPullRequests(PullRequestCollection $pullRequests): self
    {
        return $this->whereIn('pull_request_id', $pullRequests->modelKeys());
    }
}
