<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Composer;

use Maarheeze\Compass\Collector\Composer\ComposerType;
use Maarheeze\Compass\Models\Repository;

readonly class RepositoryKindClassifier
{
    public function classify(Repository $repository): RepositoryKind
    {
        $composerType = $this->composerType($repository);

        if ($composerType === null) {
            return RepositoryKind::OTHER;
        }

        foreach (RepositoryKind::cases() as $kind) {
            if ($kind->composerType() === $composerType) {
                return $kind;
            }
        }

        return RepositoryKind::OTHER;
    }

    private function composerType(Repository $repository): ?ComposerType
    {
        $composerJsonType = $repository->composerJson?->type;

        if ($repository->composer_json_version === null || $composerJsonType === null) {
            return null;
        }

        return ComposerType::tryFrom($composerJsonType);
    }
}
