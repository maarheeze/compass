<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Lock;

use Maarheeze\Compass\Collections\LockedPackageCollection;

readonly class ComposerLockSummary
{
    public function __construct(
        public ?string $phpConstraint,
        public LockedPackageCollection $lockedPackages,
    ) {
    }
}
