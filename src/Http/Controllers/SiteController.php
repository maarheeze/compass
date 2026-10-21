<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Models\Site;

readonly class SiteController
{
    public function __construct(
        private ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('compass::sites.index', [
            'sites' => Site::query()
                ->with('repository')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Site $site): View
    {
        $site->load('repository');

        return $this->view->make('compass::sites.show', [
            'site' => $site,
        ]);
    }
}
