<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\PullRequests;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedAssigneeCollection;

readonly class CollectedPullRequest
{
    public function __construct(
        public string $externalId,
        public string $repositoryLookupKey,
        public string $externalReference,
        public string $title,
        public ?string $sourceUrl,
        public ?string $authorLogin,
        public bool $isDraft,
        public CarbonImmutable $openedAt,
        public CollectedAssigneeCollection $assignees,
    ) {
    }
}
