<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;

class RepositoryMissingNotice extends Component
{
    public readonly string $deleteUrl;
    public readonly string $ignoreUrl;
    public readonly ?CarbonImmutable $missingAt;
    public readonly ?CarbonImmutable $missingIgnoredAt;

    public function __construct(
        Repository $repository,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->deleteUrl = $url->route(RouteName::GITHUB_REPOSITORY_DELETE->value, $repository);
        $this->ignoreUrl = $url->route(RouteName::GITHUB_REPOSITORY_IGNORE->value, $repository);
        $this->missingAt = $repository->missing_at;
        $this->missingIgnoredAt = $repository->missing_ignored_at;
    }

    public function render(): View
    {
        return $this->view->make('compass::components.missing-notice');
    }

    public function shouldRender(): bool
    {
        return $this->missingAt !== null;
    }
}
