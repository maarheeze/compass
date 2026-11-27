<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealthClassifier;
use Maarheeze\Compass\Collections\SiteScheduledTaskCollection;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteScheduledTask;
use Maarheeze\Compass\View\CronDescription;

class SiteScheduledTaskTable extends Component
{
    public readonly SiteScheduledTaskCollection $scheduledTasks;

    public function __construct(
        Site $site,
        private readonly CronDescription $cronDescription,
        SchedulerHealthClassifier $schedulerHealthClassifier,
        private readonly ViewFactory $view,
    ) {
        $scheduledTasks = new SiteScheduledTaskCollection();

        foreach ($site->reports as $siteReport) {
            if (!$schedulerHealthClassifier->classify($siteReport)->isJudged()) {
                continue;
            }

            $scheduledTasks = $scheduledTasks->merge($siteReport->scheduledTasks);
        }

        $this->scheduledTasks = $scheduledTasks;
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-scheduled-task-table');
    }

    public function timing(SiteScheduledTask $siteScheduledTask): string
    {
        return $this->cronDescription->describe($siteScheduledTask->expression);
    }
}
