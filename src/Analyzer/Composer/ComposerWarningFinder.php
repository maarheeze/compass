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
        $repositories = Assert::isInstanceOf(
            Repository::query()
                ->with('composerJson')
                ->orderBy('name')
                ->get(),
            RepositoryCollection::class,
        );

        return $repositories->filter(function (Repository $repository): bool {
            return $this->composerWarningDetector->detect($repository) !== null;
        });
    }
}
