<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\GitHub\GitHubPullRequest;

/**
 * @extends Collection<int, GitHubPullRequest>
 */
class GitHubPullRequestCollection extends Collection
{
    /**
     * @return Collection<int, string>
     */
    public function gitHubIds(): Collection
    {
        return $this->toBase()->map(static function (GitHubPullRequest $gitHubPullRequest): string {
            return $gitHubPullRequest->gitHubId;
        });
    }

    /**
     * @return Collection<int, string>
     */
    public function repositoryNames(): Collection
    {
        return $this->toBase()->map(static function (GitHubPullRequest $gitHubPullRequest): string {
            return $gitHubPullRequest->repositoryName;
        });
    }
}
