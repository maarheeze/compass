<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealth;
use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealthClassifier;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\BadgeType;

class SiteQueueWorkerFact extends Component
{
    public readonly BadgeType $badgeType;
    public readonly ?CarbonImmutable $collectedAt;
    public readonly ?QueueWorkerState $notRunningQueueWorkerState;
    public readonly QueueWorkerHealth $queueWorkerHealth;

    public function __construct(
        Site $site,
        QueueWorkerHealthClassifier $queueWorkerHealthClassifier,
        private readonly ViewFactory $view,
    ) {
        $this->queueWorkerHealth = $queueWorkerHealthClassifier->classify($site);
        $this->badgeType = match ($this->queueWorkerHealth) {
            QueueWorkerHealth::NOT_RUNNING => BadgeType::DANGER,
            QueueWorkerHealth::RUNNING => BadgeType::SUCCESS,
            QueueWorkerHealth::NOT_NEEDED, QueueWorkerHealth::UNKNOWN => BadgeType::NEUTRAL,
        };
        $this->notRunningQueueWorkerState = $this->queueWorkerHealth === QueueWorkerHealth::NOT_RUNNING
            ? $site->queue_worker_state
            : null;
        $this->collectedAt = match ($this->queueWorkerHealth) {
            QueueWorkerHealth::NOT_RUNNING, QueueWorkerHealth::RUNNING => $site->processes_collected_at,
            QueueWorkerHealth::NOT_NEEDED, QueueWorkerHealth::UNKNOWN => null,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-queue-worker-fact');
    }
}
