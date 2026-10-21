<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

use Maarheeze\Compass\Analyzer\Composer\ComposerWarningFinder;
use Maarheeze\Compass\Analyzer\Forge\SiteBehindFinder;

readonly class AlertFinder
{
    public function __construct(
        private ComposerWarningFinder $composerWarningFinder,
        private SiteBehindFinder $siteBehindFinder,
    ) {
    }

    public function find(): Alerts
    {
        return new Alerts(
            $this->composerWarningFinder->findRepositories(),
            $this->siteBehindFinder->findSites(),
        );
    }
}
