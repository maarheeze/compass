<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer;

use Maarheeze\Compass\Collector\CollectingFailed;

interface ComposerFilesCollector
{
    /**
     * @throws CollectingFailed
     */
    public function content(string $repositoryName, string $version): string;
}
