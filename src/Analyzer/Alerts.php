<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\SiteCollection;

readonly class Alerts
{
    public function __construct(
        public RepositoryCollection $repositoriesWithComposerWarning,
        public SiteCollection $sitesBehind,
    ) {
    }

    public function isEmpty(): bool
    {
        if ($this->repositoriesWithComposerWarning->isNotEmpty()) {
            return false;
        }

        return $this->sitesBehind->isEmpty();
    }
}
