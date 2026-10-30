<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\GitHub\GitHubIssue;

/**
 * @extends Collection<int, GitHubIssue>
 */
class GitHubIssueCollection extends Collection
{
    /**
     * @return Collection<int, string>
     */
    public function gitHubIds(): Collection
    {
        return $this->toBase()->map(static function (GitHubIssue $gitHubIssue): string {
            return $gitHubIssue->gitHubId;
        });
    }

    /**
     * @return Collection<int, string>
     */
    public function repositoryNames(): Collection
    {
        return $this->toBase()->map(static function (GitHubIssue $gitHubIssue): string {
            return $gitHubIssue->repositoryName;
        });
    }
}
