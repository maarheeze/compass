<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\SiteCollection;

readonly class Alerts
{
    public function __construct(
        public RepositoryCollection $missingRepositories,
        public SiteCollection $missingSites,
        public RepositoryCollection $repositoriesWithComposerWarning,
        public SiteCollection $sitesBehind,
        public SiteCollection $sitesOutsideProduction,
        public SiteCollection $sitesWithDebugEnabled,
        public SiteCollection $sitesWithUnauthorizedClient,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->missingRepositories->isEmpty()
            && $this->missingSites->isEmpty()
            && $this->repositoriesWithComposerWarning->isEmpty()
            && $this->sitesBehind->isEmpty()
            && $this->sitesOutsideProduction->isEmpty()
            && $this->sitesWithDebugEnabled->isEmpty()
            && $this->sitesWithUnauthorizedClient->isEmpty();
    }
}
