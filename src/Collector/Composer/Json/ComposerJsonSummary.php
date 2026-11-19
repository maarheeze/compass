<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Json;

use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;

readonly class ComposerJsonSummary
{
    public function __construct(
        public ?string $type,
        public ?string $phpConstraint,
        public ?string $minimumStability,
        public bool $preferStable,
        public ComposerJsonPackageCollection $requiredPackages,
    ) {
    }
}
