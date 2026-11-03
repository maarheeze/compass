<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\GitHubPullRequestCollection;

readonly class IssuesAndPullRequestsQueryResult
{
    public function __construct(
        public CollectedIssueCollection $collectedIssues,
        public GitHubPullRequestCollection $gitHubPullRequests,
        public ?string $nextPageUrl,
    ) {
    }
}
