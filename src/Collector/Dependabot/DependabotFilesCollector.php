<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

interface DependabotFilesCollector
{
    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function content(string $repositoryName, string $version): string;
}
