<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Analyzer\Client\DebugEnabledSiteFinder;
use Maarheeze\Compass\Analyzer\Client\NonProductionSiteFinder;
use Maarheeze\Compass\Analyzer\Client\UnauthorizedClientSiteFinder;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningFinder;
use Maarheeze\Compass\Analyzer\Forge\MissingSiteFinder;
use Maarheeze\Compass\Analyzer\Forge\SiteBehindFinder;
use Maarheeze\Compass\Analyzer\GitHub\MissingRepositoryFinder;

readonly class AlertFinder
{
    public function __construct(
        private ComposerWarningFinder $composerWarningFinder,
        private DebugEnabledSiteFinder $debugEnabledSiteFinder,
        private MissingRepositoryFinder $missingRepositoryFinder,
        private MissingSiteFinder $missingSiteFinder,
        private NonProductionSiteFinder $nonProductionSiteFinder,
        private SiteBehindFinder $siteBehindFinder,
        private UnauthorizedClientSiteFinder $unauthorizedClientSiteFinder,
    ) {
    }

    public function find(): Alerts
    {
        return new Alerts(
            $this->missingRepositoryFinder->findRepositories(),
            $this->missingSiteFinder->findSites(),
            $this->composerWarningFinder->findRepositories(),
            $this->siteBehindFinder->findSites(),
            $this->nonProductionSiteFinder->findSites(),
            $this->debugEnabledSiteFinder->findSites(),
            $this->unauthorizedClientSiteFinder->findSites(),
        );
    }
}
