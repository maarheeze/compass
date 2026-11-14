<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Alerts\AlertAssigner;
use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertDoneMarker;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertMuter;
use Maarheeze\Compass\Alerts\AlertReopener;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\Requests\AlertActionRequest;
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
        private ResponseFactory $responseFactory,
        private ViewFactory $view,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function assign(Alert $alert, AssignAlertRequest $assignAlertRequest): RedirectResponse|Response
    {
        $this->alertAssigner->assignToMe($alert, $this->authenticatedUser->user());

        return $this->actionResponse(
            $assignAlertRequest,
            AlertHistoryEntryType::ASSIGNED,
            $this->redirector->route(RouteName::ALERT, $alert),
        );
    }

    /**
     * @throws Throwable
     */
    public function markDone(Alert $alert, MarkAlertDoneRequest $markAlertDoneRequest): RedirectResponse|Response
    {
        $this->alertDoneMarker->markDone($alert, $this->authenticatedUser->user());

        return $this->actionResponse(
            $markAlertDoneRequest,
            AlertHistoryEntryType::MARKED_DONE,
            $this->redirector->route(RouteName::DASHBOARD),
        );
    }

    /**
     * @throws Throwable
     */
    public function mute(Alert $alert, MuteAlertRequest $muteAlertRequest): RedirectResponse|Response
    {
        $this->alertMuter->mute($alert, $this->authenticatedUser->user());

        return $this->actionResponse(
            $muteAlertRequest,
            AlertHistoryEntryType::MUTED,
            $this->redirector->route(RouteName::ALERT, $alert),
        );
    }

    /**
     * @throws Throwable
     */
    public function reopen(Alert $alert, ReopenAlertRequest $reopenAlertRequest): RedirectResponse|Response
    {
        $this->alertReopener->reopen($alert, $this->authenticatedUser->user());

        return $this->actionResponse(
            $reopenAlertRequest,
            AlertHistoryEntryType::REOPENED,
            $this->redirector->route(RouteName::ALERT, $alert),
        );
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
    public function unassign(Alert $alert, UnassignAlertRequest $unassignAlertRequest): RedirectResponse|Response
    {
        $this->alertAssigner->unassign($alert, $this->authenticatedUser->user());

        return $this->actionResponse(
            $unassignAlertRequest,
            AlertHistoryEntryType::UNASSIGNED,
            $this->redirector->route(RouteName::ALERT, $alert),
        );
    }

    /**
     * @throws Throwable
     */
    public function unmute(Alert $alert, UnmuteAlertRequest $unmuteAlertRequest): RedirectResponse|Response
    {
        $this->alertMuter->unmute($alert, $this->authenticatedUser->user());

        return $this->actionResponse(
            $unmuteAlertRequest,
            AlertHistoryEntryType::UNMUTED,
            $this->redirector->route(RouteName::ALERT, $alert),
        );
    }

    private function actionResponse(
        AlertActionRequest $alertActionRequest,
        AlertHistoryEntryType $performedEntryType,
        RedirectResponse $redirectResponse,
    ): RedirectResponse|Response {
        if ($alertActionRequest->expectsJson()) {
            return $this->responseFactory->noContent();
        }

        $this->alertConfirmation->flash($performedEntryType);

        return $redirectResponse;
    }
}
