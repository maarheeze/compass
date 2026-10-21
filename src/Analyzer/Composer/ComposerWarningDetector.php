<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

readonly class ComposerWarningDetector
{
    public function __construct(
        private RepositoryKindClassifier $repositoryKindClassifier,
    ) {
    }

    public function detect(Repository $repository): ?ComposerWarning
    {
        if ($repository->composer_json_oid === null) {
            return null;
        }

        $composerJson = $repository->composerJson;
        if ($composerJson === null) {
            return null;
        }

        return match ($this->repositoryKindClassifier->classify($repository)) {
            RepositoryKind::LIBRARY => $this->detectLibraryWarning($repository),
            RepositoryKind::OTHER => $this->detectTypeWarning($composerJson),
            RepositoryKind::PROJECT => $this->detectProjectWarning($repository),
        };
    }

    private function detectLibraryWarning(Repository $repository): ?ComposerWarning
    {
        if ($repository->composer_lock_oid === null) {
            return null;
        }

        return ComposerWarning::LIBRARY_WITH_LOCK;
    }

    private function detectProjectWarning(Repository $repository): ?ComposerWarning
    {
        if ($repository->composer_lock_oid !== null) {
            return null;
        }

        return ComposerWarning::PROJECT_WITHOUT_LOCK;
    }

    private function detectTypeWarning(ComposerJson $composerJson): ComposerWarning
    {
        if ($composerJson->type === null) {
            return ComposerWarning::MISSING_TYPE;
        }

        return ComposerWarning::UNKNOWN_TYPE;
    }
}
