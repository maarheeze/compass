<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\RepositoryFilter;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\FilterOption;
use Webmozart\Assert\Assert;

/**
 * @extends Collection<array-key, Repository>
 */
class RepositoryCollection extends Collection
{
    public function kindFilterOptions(
        RepositoryKindClassifier $repositoryKindClassifier,
        Translator $translator,
    ): FilterOptionCollection {
        $kindFilterOptions = new FilterOptionCollection();

        foreach (RepositoryKind::displayOrder() as $kind) {
            $kindFilterOptions->push(new FilterOption(
                Assert::string($translator->get($kind->labelKey())),
                $this->ofKind($kind, $repositoryKindClassifier)->count(),
                [RepositoryFilter::KIND_PARAMETER => $kind->value],
                null,
            ));
        }

        return $kindFilterOptions;
    }

    public function ofKind(RepositoryKind $kind, RepositoryKindClassifier $repositoryKindClassifier): self
    {
        return $this->filter(static function (Repository $repository) use ($kind, $repositoryKindClassifier): bool {
            return $repositoryKindClassifier->classify($repository) === $kind;
        });
    }

    public function ofVisibility(RepositoryVisibility $visibility): self
    {
        return $this->filter(static function (Repository $repository) use ($visibility): bool {
            return $repository->visibility === $visibility;
        });
    }

    public function visibilityFilterOptions(Translator $translator): FilterOptionCollection
    {
        $visibilityFilterOptions = new FilterOptionCollection();

        foreach (RepositoryVisibility::displayOrder() as $visibility) {
            $visibilityFilterOptions->push(new FilterOption(
                Assert::string($translator->get($visibility->labelKey())),
                $this->ofVisibility($visibility)->count(),
                [RepositoryFilter::VISIBILITY_PARAMETER => $visibility->value],
                null,
            ));
        }

        return $visibilityFilterOptions;
    }
}
