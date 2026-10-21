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
        $sites = Assert::isInstanceOf(
            Site::query()
                ->with('repository')
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );

        return $sites->filter(function (Site $site): bool {
            return $this->deploymentStateClassifier->classify($site) === DeploymentState::BEHIND;
        });
    }
}
