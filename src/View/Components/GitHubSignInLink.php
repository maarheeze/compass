<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;

class GitHubSignInLink extends Component
{
    public readonly string $url;

    public function __construct(
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::GITHUB_REDIRECT->value);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.git-hub-sign-in-link');
    }
}
