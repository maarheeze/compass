<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Dependabot;

use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollector;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Models\Repository;

readonly class DependabotCoverageChecker
{
    public function __construct(
        private RepositoryKindClassifier $repositoryKindClassifier,
        private SourceDrivers $sourceDrivers,
    ) {
    }

    public function isRequired(Repository $repository, PackageEcosystem $packageEcosystem): bool
    {
        if (!$this->sourceDrivers->hasCollector($repository->source, DependabotFilesCollector::class)) {
            return false;
        }

        return match ($packageEcosystem) {
            PackageEcosystem::COMPOSER => $this->isProject($repository),
            PackageEcosystem::NPM => $repository->package_json_version !== null,
        };
    }

    public function isUncovered(Repository $repository, PackageEcosystem $packageEcosystem): bool
    {
        if (!$this->isRequired($repository, $packageEcosystem)) {
            return false;
        }

        $dependabotYml = $repository->dependabotYml;

        if ($dependabotYml === null) {
            return true;
        }

        return !$dependabotYml->package_ecosystems->contains($packageEcosystem->value);
    }

    private function isProject(Repository $repository): bool
    {
        return $this->repositoryKindClassifier->classify($repository) === RepositoryKind::PROJECT;
    }
}
