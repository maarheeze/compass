<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

class AlertSubjectLink extends Component
{
    public readonly string $name;
    public readonly string $url;

    public function __construct(
        Repository|Site|SiteEndpoint|SiteReport $subject,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->name = $subject->displayName();
        $this->url = match (true) {
            $subject instanceof Repository => $url->route(RouteName::GITHUB_REPOSITORY->value, $subject),
            $subject instanceof Site => $url->route(RouteName::FORGE_SITE->value, $subject),
            $subject instanceof SiteEndpoint => $url->route(
                RouteName::FORGE_SITE_ENDPOINT_CHECKS->value,
                [$subject->siteReport->site, new EndpointPath($subject->path)->routeKey()],
            ),
            $subject instanceof SiteReport => $url->route(RouteName::FORGE_SITE->value, $subject->site),
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-subject-link');
    }
}
