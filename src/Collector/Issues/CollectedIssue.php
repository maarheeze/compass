<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedAssigneeCollection;

readonly class CollectedIssue
{
    public function __construct(
        public string $externalId,
        public string $repositoryLookupKey,
        public int $number,
        public string $title,
        public string $url,
        public ?string $authorLogin,
        public CarbonImmutable $openedAt,
        public CollectedAssigneeCollection $assignees,
    ) {
    }
}
