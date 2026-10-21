<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\Composer;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarning;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningDetector;
use Maarheeze\Compass\Models\Repository;

class WarningBadge extends Component
{
    public readonly ?ComposerWarning $composerWarning;

    public function __construct(
        Repository $repository,
        ComposerWarningDetector $composerWarningDetector,
        private readonly ViewFactory $view,
    ) {
        $this->composerWarning = $composerWarningDetector->detect($repository);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.composer.warning-badge');
    }
}
