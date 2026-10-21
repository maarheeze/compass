<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Models\Repository;

readonly class RepositoryController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('compass::repositories.index', [
            'repositories' => Repository::query()->orderBy('name')->get(),
            'user' => $this->authenticatedUser->user(),
        ]);
    }

    public function show(Repository $repository): View
    {
        $orderPackagesByName = static function (Builder $packageQuery): void {
            $packageQuery->orderBy('name');
        };
        $repository->load([
            'composerJson.packages' => $orderPackagesByName,
            'composerLock.packages' => $orderPackagesByName,
        ]);

        return $this->view->make('compass::repositories.show', [
            'repository' => $repository,
            'user' => $this->authenticatedUser->user(),
        ]);
    }
}
