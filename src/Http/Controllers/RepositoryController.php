<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

readonly class RepositoryController
{
    public function __construct(
        private RepositoryKindClassifier $repositoryKindClassifier,
        private ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        $repositories = Assert::isInstanceOf(
            Repository::query()
                ->with('composerJson')
                ->orderBy('name')
                ->get(),
            RepositoryCollection::class,
        );

        return $this->view->make('compass::repositories.index', [
            'libraries' => $this->getRepositoriesOfKind($repositories, RepositoryKind::LIBRARY),
            'otherRepositories' => $this->getRepositoriesOfKind($repositories, RepositoryKind::OTHER),
            'projects' => $this->getRepositoriesOfKind($repositories, RepositoryKind::PROJECT),
        ]);
    }

    public function show(Repository $repository): View
    {
        $orderPackagesByName = static function (Builder $packageQuery): void {
            $packageQuery->orderBy('name');
        };
        $repository->load([
            'composerJson.developmentPackages' => $orderPackagesByName,
            'composerJson.productionPackages' => $orderPackagesByName,
            'composerLock.packages' => $orderPackagesByName,
            'issues' => static function (Builder $issueQuery): void {
                $issueQuery->latest('opened_at');
            },
            'issues.assignees',
            'pullRequests' => static function (Builder $pullRequestQuery): void {
                $pullRequestQuery->latest('opened_at');
            },
            'pullRequests.assignees',
            'sites' => static function (Builder $siteQuery): void {
                $siteQuery->orderBy('name');
            },
            'sites.repository',
        ]);

        return $this->view->make('compass::repositories.show', [
            'kind' => $this->repositoryKindClassifier->classify($repository),
            'repository' => $repository,
        ]);
    }

    private function getRepositoriesOfKind(RepositoryCollection $repositories, RepositoryKind $kind): RepositoryCollection
    {
        return $repositories->filter(function (Repository $repository) use ($kind): bool {
            return $this->repositoryKindClassifier->classify($repository) === $kind;
        });
    }
}
