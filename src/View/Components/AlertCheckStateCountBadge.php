<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertCheckStateCount;
use Maarheeze\Compass\View\AlertCheckStateBadgeType;
use Maarheeze\Compass\View\BadgeType;

use function sprintf;

class AlertCheckStateCountBadge extends Component
{
    public readonly BadgeType $badgeType;
    public readonly string $label;

    public function __construct(
        AlertCheckStateCount $alertCheckStateCount,
        AlertCheckStateBadgeType $alertCheckStateBadgeType,
        Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $this->badgeType = $alertCheckStateBadgeType->badgeType($alertCheckStateCount->alertCheckState);
        $this->label = $translator->choice(
            sprintf('compass::alert-check-state-count.%s', $alertCheckStateCount->alertCheckState->name),
            $alertCheckStateCount->count,
            ['count' => $alertCheckStateCount->count],
        );
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-check-state-count-badge');
    }
}
