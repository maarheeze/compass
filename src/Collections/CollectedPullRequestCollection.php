<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\PullRequests\CollectedPullRequest;

/**
 * @extends Collection<int, CollectedPullRequest>
 */
class CollectedPullRequestCollection extends Collection
{
    /**
     * @return Collection<int, string>
     */
    public function externalIds(): Collection
    {
        return $this->toBase()->map(static function (CollectedPullRequest $collectedPullRequest): string {
            return $collectedPullRequest->externalId;
        });
    }

    /**
     * @return Collection<int, string>
     */
    public function repositoryNames(): Collection
    {
        return $this->toBase()->map(static function (CollectedPullRequest $collectedPullRequest): string {
            return $collectedPullRequest->repositoryName;
        });
    }
}
