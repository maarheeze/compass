<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Carbon\CarbonImmutable;

readonly class CollectedIssue
{
    /**
     * @param list<string> $assigneeLogins
     */
    public function __construct(
        public string $externalId,
        public string $repositoryName,
        public int $number,
        public string $title,
        public string $url,
        public ?string $authorLogin,
        public CarbonImmutable $openedAt,
        public array $assigneeLogins,
    ) {
    }
}
