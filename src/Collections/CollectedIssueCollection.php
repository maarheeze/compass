<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Issues\CollectedIssue;

/**
 * @extends Collection<int, CollectedIssue>
 */
class CollectedIssueCollection extends Collection
{
    /**
     * @return Collection<int, string>
     */
    public function externalIds(): Collection
    {
        return $this->toBase()->map(static function (CollectedIssue $collectedIssue): string {
            return $collectedIssue->externalId;
        });
    }

    /**
     * @return Collection<int, string>
     */
    public function repositoryLookupKeys(): Collection
    {
        return $this->toBase()->map(static function (CollectedIssue $collectedIssue): string {
            return $collectedIssue->repositoryLookupKey;
        });
    }
}
