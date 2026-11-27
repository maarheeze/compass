<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealth;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealthClassifier;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\BadgeType;

class SiteSchedulerFact extends Component
{
    public readonly BadgeType $badgeType;
    public readonly ?int $scheduledTaskCount;
    public readonly SchedulerHealth $schedulerHealth;

    public function __construct(
        public readonly SiteReport $siteReport,
        SchedulerHealthClassifier $schedulerHealthClassifier,
        private readonly ViewFactory $view,
    ) {
        $this->schedulerHealth = $schedulerHealthClassifier->classify($siteReport);
        $this->badgeType = match ($this->schedulerHealth) {
            SchedulerHealth::NOT_RUNNING => BadgeType::DANGER,
            SchedulerHealth::RUNNING => BadgeType::SUCCESS,
            SchedulerHealth::NO_TASKS, SchedulerHealth::UNKNOWN => BadgeType::NEUTRAL,
        };
        $this->scheduledTaskCount = $this->schedulerHealth->isJudged() ? $siteReport->scheduledTasks->count() : null;
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-scheduler-fact');
    }
}
