<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

readonly class GitHubRepository
{
    public function __construct(
        public string $gitHubId,
        public string $name,
        public string $defaultBranch,
        public string $lastCommitSha,
        public RepositoryVisibility $visibility,
        public ?string $composerJsonOid,
        public ?string $composerLockOid,
    ) {
    }
}
