<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class MissingRepositoryFinder
{
    public function findRepositories(): RepositoryCollection
    {
        return Assert::isInstanceOf(
            Repository::query()
                ->whereNotNull('missing_at')
                ->whereNull('missing_ignored_at')
                ->orderBy('name')
                ->get(),
            RepositoryCollection::class,
        );
    }
}
