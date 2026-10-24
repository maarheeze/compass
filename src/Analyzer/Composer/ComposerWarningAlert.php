<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Analyzer\Alert;
use Maarheeze\Compass\Models\Repository;

readonly class ComposerWarningAlert implements Alert
{
    public function __construct(
        public Repository $repository,
    ) {
    }

    public function viewName(): string
    {
        return 'compass::components.alerts.composer-warning';
    }
}
