<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;

class IssueReference extends Component
{
    public readonly ?string $url;

    public function __construct(
        public readonly string $externalReference,
        ?Repository $repository,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        if ($repository === null) {
            $this->url = null;

            return;
        }

        $this->url = $urlGenerator->route(RouteName::REPOSITORY->value, $repository);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-reference');
    }
}
