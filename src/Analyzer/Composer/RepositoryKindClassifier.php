<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Models\Repository;

readonly class RepositoryKindClassifier
{
    public function classify(Repository $repository): RepositoryKind
    {
        if ($repository->composer_json_oid === null) {
            return RepositoryKind::OTHER;
        }

        return match ($repository->composerJson?->type) {
            'library' => RepositoryKind::LIBRARY,
            'project' => RepositoryKind::PROJECT,
            default => RepositoryKind::OTHER,
        };
    }
}
