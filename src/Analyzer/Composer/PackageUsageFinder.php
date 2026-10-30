<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\ComposerLockPackage;

readonly class PackageUsageFinder
{
    public function __construct(
        private RepositoryKindClassifier $repositoryKindClassifier,
    ) {
    }

    public function find(string $packageName): PackageUsages
    {
        return new PackageUsages(
            $this->getLockedPackagesOfProjects($packageName),
            $this->getRequiredPackagesOfLibraries($packageName),
        );
    }

    private function getLockedPackagesOfProjects(string $packageName): ComposerLockPackageCollection
    {
        $lockedPackages = ComposerLockPackage::query()
            ->with('composerLock.repository.composerJson')
            ->named($packageName)
            ->get();

        return $lockedPackages
            ->filter(function (ComposerLockPackage $lockedPackage): bool {
                return $this->repositoryKindClassifier->classify($lockedPackage->composerLock->repository) === RepositoryKind::PROJECT;
            })
            ->sortBy(static function (ComposerLockPackage $lockedPackage): string {
                return $lockedPackage->composerLock->repository->name;
            })
            ->values();
    }

    private function getRequiredPackagesOfLibraries(string $packageName): ComposerJsonPackageCollection
    {
        $requiredPackages = ComposerJsonPackage::query()
            ->with('composerJson.repository.composerJson')
            ->named($packageName)
            ->get();

        return $requiredPackages
            ->filter(function (ComposerJsonPackage $requiredPackage): bool {
                return $this->repositoryKindClassifier->classify($requiredPackage->composerJson->repository) === RepositoryKind::LIBRARY;
            })
            ->sortBy(static function (ComposerJsonPackage $requiredPackage): string {
                return $requiredPackage->composerJson->repository->name;
            })
            ->values();
    }
}
