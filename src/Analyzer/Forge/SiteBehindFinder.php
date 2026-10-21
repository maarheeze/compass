<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Forge;

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

readonly class SiteBehindFinder
{
    public function __construct(
        private DeploymentStateClassifier $deploymentStateClassifier,
    ) {
    }

    public function findSites(): SiteCollection
    {
        $activeSites = Assert::isInstanceOf(
            Site::query()
                ->with('repository')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );

        return $activeSites->filter(function (Site $site): bool {
            return $this->deploymentStateClassifier->classify($site) === DeploymentState::BEHIND;
        });
    }
}
