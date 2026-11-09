<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Models\Alert;

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
                ->withSubject()
                ->latest()
                ->get(),
        ]);
    }
}
