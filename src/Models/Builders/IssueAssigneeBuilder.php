<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Models\IssueAssignee;

/**
 * @extends Builder<IssueAssignee>
 */
class IssueAssigneeBuilder extends Builder
{
    public function ofIssues(IssueCollection $issues): self
    {
        return $this->whereIn('issue_id', $issues->modelKeys());
    }
}
