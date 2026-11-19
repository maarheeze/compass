<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\View\RepositoryGroup;
use Webmozart\Assert\Assert;

class RepositoryGroupList extends Component
{
    public readonly string $iconComponent;
    public readonly string $title;

    public function __construct(
        public readonly RepositoryGroup $repositoryGroup,
        Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $this->iconComponent = match ($repositoryGroup->kind) {
            RepositoryKind::LIBRARY => 'compass::icons.package',
            RepositoryKind::OTHER => 'compass::icons.file-directory',
            RepositoryKind::PROJECT => 'compass::icons.repo',
        };
        $this->title = Assert::string($translator->get(match ($repositoryGroup->kind) {
            RepositoryKind::LIBRARY => 'compass::repository.libraries',
            RepositoryKind::OTHER => 'compass::repository.other',
            RepositoryKind::PROJECT => 'compass::repository.projects',
        }));
    }

    public function render(): View
    {
        return $this->view->make('compass::components.repository-group-list');
    }
}
