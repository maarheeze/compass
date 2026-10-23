<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\GitHubCommitUrlFactory;
use Maarheeze\Compass\Models\Repository;

class CommitHash extends Component
{
    public readonly ?string $url;

    public function __construct(
        ?Repository $repository,
        public readonly ?string $commitHash,
        GitHubCommitUrlFactory $gitHubCommitUrlFactory,
        private readonly ViewFactory $view,
    ) {
        $this->url = match (true) {
            $repository === null, $commitHash === null => null,
            default => $gitHubCommitUrlFactory->create($repository, $commitHash),
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.commit-hash');
    }
}
