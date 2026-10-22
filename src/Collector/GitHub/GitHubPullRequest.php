<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;

readonly class GitHubPullRequest
{
    /**
     * @param list<string> $assigneeLogins
     */
    public function __construct(
        public string $gitHubId,
        public string $repositoryName,
        public int $number,
        public string $title,
        public string $url,
        public ?string $authorLogin,
        public bool $isDraft,
        public CarbonImmutable $openedAt,
        public array $assigneeLogins,
    ) {
    }
}
