<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Maarheeze\Compass\Collector\Composer\ComposerFilesCollector;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollector;

class RecordingRepositoryFilesCollector implements ComposerFilesCollector, DependabotFilesCollector
{
    /** @var list<array{repositoryName: string, version: string}> */
    public array $requestedFiles = [];

    public function __construct(
        private readonly string $content,
    ) {
    }

    public function content(string $repositoryName, string $version): string
    {
        $this->requestedFiles[] = [
            'repositoryName' => $repositoryName,
            'version' => $version,
        ];

        return $this->content;
    }
}
