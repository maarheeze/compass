<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertLevelCount;
use Maarheeze\Compass\View\AlertLevelBadgeType;
use Maarheeze\Compass\View\BadgeType;

use function sprintf;

class AlertLevelCountBadge extends Component
{
    public readonly BadgeType $badgeType;
    public readonly string $label;

    public function __construct(
        AlertLevelCount $alertLevelCount,
        AlertLevelBadgeType $alertLevelBadgeType,
        Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $this->badgeType = $alertLevelBadgeType->badgeType($alertLevelCount->alertLevel);
        $this->label = $translator->choice(
            sprintf('compass::alert-level-count.%s', $alertLevelCount->alertLevel->name),
            $alertLevelCount->count,
            ['count' => $alertLevelCount->count],
        );
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-level-count-badge');
    }
}
