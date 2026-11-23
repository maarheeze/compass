<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\Sites\MissingSiteResolver;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Http\Requests\SiteFilterRequest;
use Maarheeze\Compass\Http\Requests\UpdateSiteRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Throwable;

readonly class SiteController
{
    public function __construct(
        private ItemsPerPage $itemsPerPage,
        private MissingSiteResolver $missingSiteResolver,
        private Redirector $redirector,
        private ResponseFactory $responseFactory,
        private RunRecorder $runRecorder,
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function delete(Site $site): RedirectResponse
    {
        $this->missingSiteResolver->delete($site);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    public function edit(Site $site, Request $request): View
    {
        return $this->view->make('compass::sites.edit', [
            'key' => $request->old('key', $site->key),
            'site' => $site,
        ]);
    }

    public function ignore(Site $site): RedirectResponse
    {
        $this->missingSiteResolver->ignore($site);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    public function index(SiteFilterRequest $siteFilterRequest): View
    {
        $siteFilter = $siteFilterRequest->siteFilter();

        return $this->view->make('compass::sites.index', [
            'phpVersionFilter' => $siteFilter->phpVersionFilter(Site::query(), $this->translator),
            'serverFilter' => $siteFilter->serverFilter(Site::query(), $this->translator),
            'sites' => $siteFilter
                ->sites(Site::query())
                ->listedPage($this->itemsPerPage),
        ]);
    }

    public function inspect(Site $site, Request $request): RedirectResponse|Response
    {
        $this->runRecorder->startSiteInspections($site);

        if ($request->expectsJson()) {
            return $this->responseFactory->noContent(Response::HTTP_ACCEPTED);
        }

        return $this->redirector->route(RouteName::SITE, $site);
    }

    public function show(Site $site): View
    {
        $site->load(['domains', 'reports', 'repository']);

        return $this->view->make('compass::sites.show', [
            'alertChecks' => AlertCheck::query()->ofSiteOrItsReports($site)->listed(),
            'alerts' => Alert::query()->ofSite($site)->listed(),
            'inactiveSiteEndpointChecks' => $site->endpointChecks()->listed(),
            'reportedSiteEndpoints' => SiteEndpoint::query()->ofSite($site)->listed(),
            'site' => $site,
        ]);
    }

    public function update(Site $site, UpdateSiteRequest $updateSiteRequest): RedirectResponse
    {
        $site->update($updateSiteRequest->validated());

        return $this->redirector->route(RouteName::SITE, $site);
    }
}
