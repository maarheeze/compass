<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Alerts\AlertDoneMarker;
use Maarheeze\Compass\Alerts\AlertReopener;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;
use Throwable;

readonly class AlertController
{
    public function __construct(
        private AlertDoneMarker $alertDoneMarker,
        private AlertReopener $alertReopener,
        private AuthenticatedUser $authenticatedUser,
        private Redirector $redirector,
        private ViewFactory $view,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function markDone(Alert $alert): RedirectResponse
    {
        $this->alertDoneMarker->markDone($alert, $this->authenticatedUser->user());

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    /**
     * @throws Throwable
     */
    public function reopen(Alert $alert): RedirectResponse
    {
        $this->alertReopener->reopen($alert, $this->authenticatedUser->user());

        return $this->redirector->route(RouteName::ALERT, $alert);
    }

    public function show(Alert $alert): View
    {
        $alert->loadMorph('subject', [SiteEndpoint::class => ['site']]);

        return $this->view->make('compass::alerts.show', [
            'alert' => $alert,
            'historyEntries' => $alert
                ->historyEntries()
                ->latest('occurred_at')
                ->get(),
        ]);
    }
}
