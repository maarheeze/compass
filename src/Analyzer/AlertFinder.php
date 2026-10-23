<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Analyzer\Composer\ComposerWarningFinder;
use Maarheeze\Compass\Analyzer\Forge\MissingSiteFinder;
use Maarheeze\Compass\Analyzer\Forge\SiteBehindFinder;
use Maarheeze\Compass\Analyzer\GitHub\MissingRepositoryFinder;

readonly class AlertFinder
{
    public function __construct(
        private ComposerWarningFinder $composerWarningFinder,
        private MissingRepositoryFinder $missingRepositoryFinder,
        private MissingSiteFinder $missingSiteFinder,
        private SiteBehindFinder $siteBehindFinder,
    ) {
    }

    public function find(): Alerts
    {
        return new Alerts(
            $this->missingRepositoryFinder->findRepositories(),
            $this->missingSiteFinder->findSites(),
            $this->composerWarningFinder->findRepositories(),
            $this->siteBehindFinder->findSites(),
        );
    }
}
