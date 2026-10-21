<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\Forge;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\ForgeSiteUrlFactory;
use Maarheeze\Compass\Models\Site;

class Link extends Component
{
    public readonly string $url;

    public function __construct(
        Site $site,
        ForgeSiteUrlFactory $forgeSiteUrlFactory,
        private readonly ViewFactory $view,
    ) {
        $this->url = $forgeSiteUrlFactory->create($site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.forge.link');
    }
}
