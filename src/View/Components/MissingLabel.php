<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LogicException;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\BadgeType;

class MissingLabel extends Component
{
    public readonly MissingState $missingState;

    public function __construct(
        Repository|Site $subject,
        private readonly ViewFactory $view,
    ) {
        $this->missingState = $subject->missingState();
    }

    public function badgeType(): BadgeType
    {
        return match ($this->missingState) {
            MissingState::COLLECTED => throw new LogicException(),
            MissingState::IGNORED => BadgeType::NEUTRAL,
            MissingState::MISSING => BadgeType::WARNING,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.missing-label');
    }

    public function shouldRender(): bool
    {
        return $this->missingState !== MissingState::COLLECTED;
    }
}
