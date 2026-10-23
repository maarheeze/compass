<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\GitHubCommitUrlFactory;
use Maarheeze\Compass\Models\Repository;

class ComparisonLink extends Component
{
    public readonly string $url;

    public function __construct(
        Repository $repository,
        string $baseCommitHash,
        GitHubCommitUrlFactory $gitHubCommitUrlFactory,
        private readonly ViewFactory $view,
    ) {
        $this->url = $gitHubCommitUrlFactory->createComparison($repository, $baseCommitHash);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.comparison-link');
    }
}
