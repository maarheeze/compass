<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Models\Builders\RepositoryBuilder;
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
        RepositoryBuilder $repositories,
        RepositoryKindClassifier $repositoryKindClassifier,
        Translator $translator,
    ): Filter {
        return new Filter(
            Assert::string($translator->get('compass::repository.kind')),
            [self::KIND_PARAMETER],
            $this
                ->repositoriesOfVisibility($repositories)
                ->listed()
                ->kindFilterOptions($repositoryKindClassifier, $translator),
        );
    }

    public function repositories(RepositoryBuilder $repositories): RepositoryBuilder
    {
        return $this->repositoriesOfVisibility($this->repositoriesOfKind($repositories));
    }

    public function visibilityFilter(RepositoryBuilder $repositories, Translator $translator): Filter
    {
        return new Filter(
            Assert::string($translator->get('compass::repository.visibility')),
            [self::VISIBILITY_PARAMETER],
            $this
                ->repositoriesOfKind($repositories)
                ->listed()
                ->visibilityFilterOptions($translator),
        );
    }

    private function repositoriesOfKind(RepositoryBuilder $repositories): RepositoryBuilder
    {
        if ($this->kind === null) {
            return $repositories;
        }

        return $repositories->ofKind($this->kind);
    }

    private function repositoriesOfVisibility(RepositoryBuilder $repositories): RepositoryBuilder
    {
        if ($this->visibility === null) {
            return $repositories;
        }

        return $repositories->ofVisibility($this->visibility);
    }
}
