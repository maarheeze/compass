<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\View\BadgeType;

class IssueTypeBadge extends Component
{
    public readonly BadgeType $badgeType;

    public function __construct(
        public readonly IssueType $issueType,
        private readonly ViewFactory $view,
    ) {
        $this->badgeType = match ($issueType) {
            IssueType::BUG => BadgeType::DANGER,
            IssueType::FEATURE => BadgeType::INFO,
            IssueType::TASK => BadgeType::WARNING,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-type-badge');
    }
}
