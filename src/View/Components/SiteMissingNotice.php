<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\Forge\MissingSiteResolver;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteMissingNotice extends Component
{
    public readonly bool $canIgnore;
    public readonly string $deleteUrl;
    public readonly string $ignoreUrl;
    public readonly ?CarbonImmutable $missingAt;
    private readonly MissingState $missingState;

    public function __construct(
        Site $site,
        MissingSiteResolver $missingSiteResolver,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->deleteUrl = $url->route(RouteName::FORGE_SITE_DELETE->value, $site);
        $this->ignoreUrl = $url->route(RouteName::FORGE_SITE_IGNORE->value, $site);
        $this->missingAt = $site->missing_at;
        $this->missingState = $site->missingState();
        $this->canIgnore = $missingSiteResolver->canIgnore($site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.missing-notice');
    }

    public function shouldRender(): bool
    {
        return $this->missingState !== MissingState::COLLECTED;
    }
}
