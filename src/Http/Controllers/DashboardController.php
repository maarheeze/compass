<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

readonly class DashboardController
{
    public function __construct(
        private ViewFactory $view,
    ) {
    }

    public function show(): View
    {
        return $this->view->make('compass::dashboard', [
            'alerts' => Alert::query()
                ->notDone()
                ->with('assignee')
                ->latest()
                ->get()
                ->loadMorph('subject', [SiteEndpoint::class => ['siteReport.site'], SiteReport::class => ['site']]),
        ]);
    }
}
