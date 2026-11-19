<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\RepositoryGroupCollection;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\View\Filter;
use Webmozart\Assert\Assert;

readonly class RepositoryFilter
{
    public const string KIND_PARAMETER = 'kind';
    public const string VISIBILITY_PARAMETER = 'visibility';

    public function __construct(
        public ?RepositoryKind $kind,
        public ?RepositoryVisibility $visibility,
    ) {
    }

    public function kindFilter(
        RepositoryCollection $repositories,
        RepositoryKindClassifier $repositoryKindClassifier,
        Translator $translator,
    ): Filter {
        return new Filter(
            Assert::string($translator->get('compass::repository.kind')),
            [self::KIND_PARAMETER],
            $this
                ->repositoriesOfVisibility($repositories)
                ->kindFilterOptions($repositoryKindClassifier, $translator),
        );
    }

    public function repositoryGroups(
        RepositoryCollection $repositories,
        RepositoryKindClassifier $repositoryKindClassifier,
    ): RepositoryGroupCollection {
        return $this
            ->repositoriesOfVisibility($repositories)
            ->repositoryGroups($this->shownKinds(), $repositoryKindClassifier);
    }

    public function visibilityFilter(
        RepositoryCollection $repositories,
        RepositoryKindClassifier $repositoryKindClassifier,
        Translator $translator,
    ): Filter {
        return new Filter(
            Assert::string($translator->get('compass::repository.visibility')),
            [self::VISIBILITY_PARAMETER],
            $this
                ->repositoriesOfKind($repositories, $repositoryKindClassifier)
                ->visibilityFilterOptions($translator),
        );
    }

    private function repositoriesOfKind(
        RepositoryCollection $repositories,
        RepositoryKindClassifier $repositoryKindClassifier,
    ): RepositoryCollection {
        if ($this->kind === null) {
            return $repositories;
        }

        return $repositories->ofKind($this->kind, $repositoryKindClassifier);
    }

    private function repositoriesOfVisibility(RepositoryCollection $repositories): RepositoryCollection
    {
        if ($this->visibility === null) {
            return $repositories;
        }

        return $repositories->ofVisibility($this->visibility);
    }

    /**
     * @return list<RepositoryKind>
     */
    private function shownKinds(): array
    {
        if ($this->kind === null) {
            return RepositoryKind::DISPLAY_ORDER;
        }

        return [$this->kind];
    }
}
