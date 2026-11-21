<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Composer;

use Maarheeze\Compass\Collector\Composer\ComposerType;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

use function array_find;

readonly class RepositoryKindClassifier
{
    public function classify(Repository $repository): RepositoryKind
    {
        $composerType = $this->composerType($repository);

        return Assert::isInstanceOf(
            array_find(RepositoryKind::cases(), static function (RepositoryKind $kind) use ($composerType): bool {
                return $kind->composerType() === $composerType;
            }),
            RepositoryKind::class,
        );
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
