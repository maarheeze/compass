<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class ComposerWarningAlertDetector implements AlertDetector
{
    public function __construct(
        private ComposerWarningDetector $composerWarningDetector,
    ) {
    }

    public function findAlerts(): AlertCollection
    {
        $repositories = Assert::isInstanceOf(
            Repository::query()
                ->with('composerJson')
                ->orderBy('name')
                ->get(),
            RepositoryCollection::class,
        );

        $repositoriesWithComposerWarning = $repositories->filter(function (Repository $repository): bool {
            return $this->composerWarningDetector->detect($repository) !== null;
        });

        return new AlertCollection(
            $repositoriesWithComposerWarning
                ->toBase()
                ->mapInto(ComposerWarningAlert::class)
                ->values(),
        );
    }
}
