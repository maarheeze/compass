<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;

readonly class PackageUsages
{
    public function __construct(
        public ComposerLockPackageCollection $lockedPackages,
        public ComposerJsonPackageCollection $requiredPackages,
    ) {
    }
}
