<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Webmozart\Assert\Assert;

class AlertRow extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly Alert $alert,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->url = $urlGenerator->route(RouteName::ALERT->value, $alert);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-row');
    }

    public function stateSince(): CarbonImmutable
    {
        return match ($this->alert->status) {
            AlertStatus::DONE => Assert::isInstanceOf($this->alert->done_at, CarbonImmutable::class),
            AlertStatus::OPEN => match ($this->alert->condition) {
                ConditionState::ACTIVE, ConditionState::NOT_APPLICABLE => $this->alert->created_at,
                ConditionState::RESOLVED => Assert::isInstanceOf($this->alert->resolved_at, CarbonImmutable::class),
            },
        };
    }
}
