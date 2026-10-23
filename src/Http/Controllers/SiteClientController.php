<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Http\Requests\UpdateClientRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

readonly class SiteClientController
{
    public function __construct(
        private Dispatcher $dispatcher,
        private Redirector $redirector,
        private ViewFactory $view,
    ) {
    }

    public function edit(Site $site, Request $request): View
    {
        return $this->view->make('compass::sites.client.edit', [
            'clientKey' => $request->old('client_key', $site->client_key),
            'clientPath' => $request->old('client_path', $site->client_path),
            'site' => $site,
        ]);
    }

    public function update(Site $site, UpdateClientRequest $updateClientRequest): RedirectResponse
    {
        $site->update($updateClientRequest->validated());
        $this->dispatcher->dispatch(new InspectSite($site));

        return $this->redirector->route(RouteName::FORGE_SITE, $site);
    }
}
