<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class SiteBehindAlertDetector implements AlertDetector
{
    public function __construct(
        private DeploymentStateClassifier $deploymentStateClassifier,
    ) {
    }

    public function findAlerts(): AlertCollection
    {
        $sites = Assert::isInstanceOf(
            Site::query()
                ->with('repository')
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );

        $sitesBehind = $sites->filter(function (Site $site): bool {
            return $this->deploymentStateClassifier->classify($site) === DeploymentState::BEHIND;
        });

        return new AlertCollection(
            $sitesBehind
                ->toBase()
                ->mapInto(SiteBehindAlert::class)
                ->values(),
        );
    }
}
