<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Composer;

use Maarheeze\Compass\Models\Repository;

readonly class RepositoryKindClassifier
{
    public function classify(Repository $repository): RepositoryKind
    {
        if ($repository->composer_json_version === null) {
            return RepositoryKind::OTHER;
        }

        $composerType = $repository->composerJson?->type;

        foreach (RepositoryKind::cases() as $kind) {
            if ($composerType !== null && $kind->composerType() === $composerType) {
                return $kind;
            }
        }

        return RepositoryKind::OTHER;
    }
}
