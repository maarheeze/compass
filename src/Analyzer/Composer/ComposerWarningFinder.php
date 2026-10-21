<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class ComposerWarningFinder
{
    public function __construct(
        private ComposerWarningDetector $composerWarningDetector,
    ) {
    }

    public function findRepositories(): RepositoryCollection
    {
        $activeRepositories = Assert::isInstanceOf(
            Repository::query()
                ->with('composerJson')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            RepositoryCollection::class,
        );

        return $activeRepositories->filter(function (Repository $repository): bool {
            return $this->composerWarningDetector->detect($repository) !== null;
        });
    }
}
