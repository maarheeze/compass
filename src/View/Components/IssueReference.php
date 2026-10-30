<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;

use function sprintf;

class IssueReference extends Component
{
    public readonly string $reference;
    public readonly string $url;

    public function __construct(
        Repository $repository,
        int $number,
        string $organization,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->reference = sprintf('%s/%s#%d', $organization, $repository->name, $number);
        $this->url = $url->route(RouteName::GITHUB_REPOSITORY->value, $repository);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-reference');
    }
}
