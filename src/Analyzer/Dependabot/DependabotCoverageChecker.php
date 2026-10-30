<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Dependabot;

use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Models\Repository;

readonly class DependabotCoverageChecker
{
    public function __construct(
        private RepositoryKindClassifier $repositoryKindClassifier,
    ) {
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

    private function isRequired(Repository $repository, PackageEcosystem $packageEcosystem): bool
    {
        return match ($packageEcosystem) {
            PackageEcosystem::COMPOSER => $this->isProject($repository),
            PackageEcosystem::NPM => $repository->package_json_oid !== null,
        };
    }
}
