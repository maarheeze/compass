<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot;

use Maarheeze\Compass\Collector\CollectingFailed;

interface DependabotFilesCollector
{
    /**
     * @throws CollectingFailed
     */
    public function content(string $repositoryName, string $version): string;
}
