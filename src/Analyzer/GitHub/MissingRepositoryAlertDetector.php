<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class MissingRepositoryAlertDetector implements AlertDetector
{
    public function findAlerts(): AlertCollection
    {
        $repositories = Assert::isInstanceOf(
            Repository::query()
                ->whereNotNull('missing_at')
                ->whereNull('missing_ignored_at')
                ->orderBy('name')
                ->get(),
            RepositoryCollection::class,
        );

        return new AlertCollection(
            $repositories
                ->toBase()
                ->mapInto(MissingRepositoryAlert::class)
                ->values(),
        );
    }
}
