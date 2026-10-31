<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Json;

use Maarheeze\Compass\Collections\RequiredPackageCollection;

readonly class ComposerJsonSummary
{
    public function __construct(
        public ?string $type,
        public ?string $phpConstraint,
        public ?string $minimumStability,
        public bool $preferStable,
        public RequiredPackageCollection $requiredPackages,
    ) {
    }
}
