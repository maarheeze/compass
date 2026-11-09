<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Routing\UrlGenerator;
use LogicException;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

readonly class AlertSubjectUrl
{
    public function __construct(
        private UrlGenerator $urlGenerator,
    ) {
    }

    public function url(AlertSubject $alertSubject): string
    {
        return match (true) {
            $alertSubject instanceof Repository => $this->urlGenerator->route(
                RouteName::REPOSITORY->value,
                $alertSubject,
            ),
            $alertSubject instanceof Site => $this->urlGenerator->route(RouteName::SITE->value, $alertSubject),
            $alertSubject instanceof SiteEndpoint => $this->urlGenerator->route(
                RouteName::SITE_ENDPOINT_CHECKS->value,
                [$alertSubject->siteReport->site, new EndpointPath($alertSubject->path)->routeKey()],
            ),
            $alertSubject instanceof SiteReport => $this->urlGenerator->route(
                RouteName::SITE->value,
                $alertSubject->site,
            ),
            default => throw new LogicException('alert subject without url'),
        };
    }
}
