<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Alerts\AlertTypeDefinitions;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

readonly class AlertController
{
    public function __construct(
        private AlertTypeDefinitions $alertTypeDefinitions,
        private ViewFactory $view,
    ) {
    }

    public function show(Alert $alert): View
    {
        $alert->loadMorph('subject', [SiteEndpoint::class => ['site']]);

        return $this->view->make('compass::alerts.show', [
            'alert' => $alert,
            'description' => $this->alertTypeDefinitions->definition($alert->type)->description($alert),
            'historyEntries' => $alert
                ->historyEntries()
                ->latest('occurred_at')
                ->get(),
        ]);
    }
}
