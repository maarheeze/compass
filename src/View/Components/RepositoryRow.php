<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;

class RepositoryRow extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly Repository $repository,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::GITHUB_REPOSITORY->value, $repository);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.repository-row');
    }
}
