<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Collections\RepositoryCollection;

readonly class RepositoryGroup
{
    public function __construct(
        public RepositoryKind $kind,
        public RepositoryCollection $repositories,
    ) {
    }
}
