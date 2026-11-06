<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Http\Requests\UpdateSiteReportRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\SiteReport;

readonly class SiteReportController
{
    public function __construct(
        private Redirector $redirector,
        private ViewFactory $view,
    ) {
    }

    public function edit(SiteReport $siteReport, Request $request): View
    {
        return $this->view->make('compass::sites.reports.edit', [
            'key' => $request->old('key', $siteReport->key),
            'path' => $request->old('path', $siteReport->path),
            'site' => $siteReport->site,
            'siteReport' => $siteReport,
        ]);
    }

    public function update(SiteReport $siteReport, UpdateSiteReportRequest $updateSiteReportRequest): RedirectResponse
    {
        $siteReport->update($updateSiteReportRequest->validated());

        return $this->redirector->route(RouteName::SITE, $siteReport->site);
    }
}
