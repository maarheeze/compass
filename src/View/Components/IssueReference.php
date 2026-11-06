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
    public readonly ?string $url;

    public function __construct(
        int $number,
        ?Repository $repository,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        if ($repository === null) {
            $this->reference = sprintf('#%d', $number);
            $this->url = null;

            return;
        }

        $this->reference = sprintf('%s#%d', $repository->full_name, $number);
        $this->url = $url->route(RouteName::REPOSITORY->value, $repository);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-reference');
    }
}
