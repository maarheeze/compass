<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;

class CheckSiteEndpointsCommand extends Command
{
    public const string SIGNATURE = 'compass:endpoints-check';

    public function handle(ClockInterface $clock, RunRecorder $runRecorder): void
    {
        $hasDueSiteEndpoints = SiteEndpoint::query()
            ->dueForCheck(CarbonImmutable::instance($clock->now()))
            ->exists();

        if (!$hasDueSiteEndpoints) {
            return;
        }

        $runRecorder->startTask(Uuid::generate(), TaskType::SITE_ENDPOINTS, new CheckSiteEndpoints());
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Check the endpoints the sites report and store the results');
    }
}
