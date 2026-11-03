<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollector;

readonly class GitHubComposerFilesCollector implements ComposerFilesCollector
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function content(string $repositoryName, string $version): string
    {
        $blobQuery = new BlobQuery($this->gitHubClient, $this->organization);

        try {
            return $blobQuery->text($repositoryName, $version);
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }
}
