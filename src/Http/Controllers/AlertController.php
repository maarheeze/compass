<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Alerts\AlertAssigner;
use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertDoneMarker;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertMuter;
use Maarheeze\Compass\Alerts\AlertReopener;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\Requests\AssignAlertRequest;
use Maarheeze\Compass\Http\Requests\MarkAlertDoneRequest;
use Maarheeze\Compass\Http\Requests\MuteAlertRequest;
use Maarheeze\Compass\Http\Requests\ReopenAlertRequest;
use Maarheeze\Compass\Http\Requests\UnassignAlertRequest;
use Maarheeze\Compass\Http\Requests\UnmuteAlertRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Throwable;

readonly class AlertController
{
    public function __construct(
        private AlertAssigner $alertAssigner,
        private AlertConfirmation $alertConfirmation,
        private AlertDoneMarker $alertDoneMarker,
        private AlertMuter $alertMuter,
        private AlertReopener $alertReopener,
        private AuthenticatedUser $authenticatedUser,
        private Redirector $redirector,
        private ViewFactory $view,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function assign(Alert $alert, AssignAlertRequest $assignAlertRequest): RedirectResponse
    {
        $this->alertAssigner->assignToMe($alert, $this->authenticatedUser->user());
        $this->alertConfirmation->flash(AlertHistoryEntryType::ASSIGNED);

        return $this->redirector->route(RouteName::ALERT, $alert);
    }

    /**
     * @throws Throwable
     */
    public function markDone(Alert $alert, MarkAlertDoneRequest $markAlertDoneRequest): RedirectResponse
    {
        $this->alertDoneMarker->markDone($alert, $this->authenticatedUser->user());
        $this->alertConfirmation->flash(AlertHistoryEntryType::MARKED_DONE);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    /**
     * @throws Throwable
     */
    public function mute(Alert $alert, MuteAlertRequest $muteAlertRequest): RedirectResponse
    {
        $this->alertMuter->mute($alert, $this->authenticatedUser->user());
        $this->alertConfirmation->flash(AlertHistoryEntryType::MUTED);

        return $this->redirector->route(RouteName::ALERT, $alert);
    }

    /**
     * @throws Throwable
     */
    public function reopen(Alert $alert, ReopenAlertRequest $reopenAlertRequest): RedirectResponse
    {
        $this->alertReopener->reopen($alert, $this->authenticatedUser->user());
        $this->alertConfirmation->flash(AlertHistoryEntryType::REOPENED);

        return $this->redirector->route(RouteName::ALERT, $alert);
    }

    public function show(Alert $alert): View
    {
        $shownAlert = Alert::query()
            ->with('assignee')
            ->withSubject()
            ->whereKey($alert->id)
            ->sole();

        return $this->view->make('compass::alerts.show', [
            'alert' => $shownAlert,
            'historyEntries' => $shownAlert
                ->historyEntries()
                ->with('user')
                ->latest('occurred_at')
                ->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function unassign(Alert $alert, UnassignAlertRequest $unassignAlertRequest): RedirectResponse
    {
        $this->alertAssigner->unassign($alert, $this->authenticatedUser->user());
        $this->alertConfirmation->flash(AlertHistoryEntryType::UNASSIGNED);

        return $this->redirector->route(RouteName::ALERT, $alert);
    }

    /**
     * @throws Throwable
     */
    public function unmute(Alert $alert, UnmuteAlertRequest $unmuteAlertRequest): RedirectResponse
    {
        $this->alertMuter->unmute($alert, $this->authenticatedUser->user());
        $this->alertConfirmation->flash(AlertHistoryEntryType::UNMUTED);

        return $this->redirector->route(RouteName::ALERT, $alert);
    }
}
