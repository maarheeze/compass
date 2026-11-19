<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Lock;

use Maarheeze\Compass\Collections\ComposerLockPackageCollection;

readonly class ComposerLockSummary
{
    public function __construct(
        public ?string $phpConstraint,
        public ComposerLockPackageCollection $lockedPackages,
    ) {
    }
}
