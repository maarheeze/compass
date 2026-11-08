<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Alerts\AlertTypeGroups;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Alerts\NotificationSubscriptionReplacer;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\Requests\UpdateSettingsRequest;
use Maarheeze\Compass\Http\RouteName;
use Throwable;

readonly class SettingsController
{
    public function __construct(
        private AlertTypeGroups $alertTypeGroups,
        private AuthenticatedUser $authenticatedUser,
        private NotificationSubscriptionReplacer $notificationSubscriptionReplacer,
        private Redirector $redirector,
        private UrlGenerator $url,
        private ViewFactory $view,
    ) {
    }

    public function edit(): View
    {
        return $this->view->make('compass::settings.edit', [
            'groupedAlertTypes' => $this->alertTypeGroups->grouped(),
            'notificationChannels' => NotificationChannel::cases(),
            'notificationSubscriptions' => $this->authenticatedUser->user()->notificationSubscriptions,
            'updateUrl' => $this->url->route(RouteName::SETTINGS_UPDATE->value),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateSettingsRequest $updateSettingsRequest): RedirectResponse
    {
        $this->notificationSubscriptionReplacer->replace(
            $this->authenticatedUser->user(),
            $updateSettingsRequest->notificationSubscriptions(),
        );

        return $this->redirector->route(RouteName::SETTINGS_EDIT);
    }
}
