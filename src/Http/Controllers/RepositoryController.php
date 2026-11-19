<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collector\Repositories\MissingRepositoryResolver;
use Maarheeze\Compass\Http\Requests\RepositoryFilterRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;
use Throwable;

readonly class RepositoryController
{
    public function __construct(
        private MissingRepositoryResolver $missingRepositoryResolver,
        private Redirector $redirector,
        private RepositoryKindClassifier $repositoryKindClassifier,
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function delete(Repository $repository): RedirectResponse
    {
        $this->missingRepositoryResolver->delete($repository);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    public function ignore(Repository $repository): RedirectResponse
    {
        $this->missingRepositoryResolver->ignore($repository);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    public function index(RepositoryFilterRequest $repositoryFilterRequest): View
    {
        $repositories = Repository::query()->listed();
        $repositoryFilter = $repositoryFilterRequest->repositoryFilter();

        return $this->view->make('compass::repositories.index', [
            'kindFilter' => $repositoryFilter->kindFilter($repositories, $this->repositoryKindClassifier, $this->translator),
            'repositoryGroups' => $repositoryFilter->repositoryGroups($repositories, $this->repositoryKindClassifier),
            'visibilityFilter' => $repositoryFilter->visibilityFilter($repositories, $this->repositoryKindClassifier, $this->translator),
        ]);
    }

    public function show(Repository $repository): View
    {
        return $this->view->make('compass::repositories.show', [
            'alertChecks' => AlertCheck::query()->ofSubject($repository)->listed(),
            'alerts' => $repository->alerts()->listed(),
            'developmentPackages' => ComposerJsonPackage::query()
                ->ofRepository($repository)
                ->development()
                ->listed(),
            'issues' => $repository->issues()->listed(),
            'kind' => $this->repositoryKindClassifier->classify($repository),
            'lockedPackages' => ComposerLockPackage::query()->ofRepository($repository)->listed(),
            'productionPackages' => ComposerJsonPackage::query()
                ->ofRepository($repository)
                ->production()
                ->listed(),
            'pullRequests' => $repository->pullRequests()->listed(),
            'repository' => $repository,
            'sites' => $repository->sites()->listed(),
        ]);
    }
}
