<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Carbon\CarbonImmutable;

readonly class CollectedRepository
{
    public function __construct(
        public string $externalId,
        public string $name,
        public string $url,
        public string $defaultBranch,
        public string $lastCommitSha,
        public CarbonImmutable $lastCommitCommittedAt,
        public RepositoryVisibility $visibility,
        public ?string $composerJsonVersion,
        public ?string $composerLockVersion,
        public ?string $packageJsonVersion,
        public ?string $dependabotYmlVersion,
    ) {
    }
}
