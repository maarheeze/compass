<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\CollectCommand;
use Webmozart\Assert\Assert;

use function array_key_exists;

class ScheduleProvider extends ServiceProvider
{
    public function boot(Repository $config): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule, Application $app) use ($config): void {
            foreach ($app->make(SourceConfigs::class)->all() as $sourceConfig) {
                $this->scheduleSource($schedule, $sourceConfig);
            }

            $this->scheduleSiteEndpoints($schedule, $config->array('compass.schedule'));
        });
    }

    private function scheduleAt(Event $event, ScheduleFrequency $scheduleFrequency): void
    {
        match ($scheduleFrequency) {
            ScheduleFrequency::EVERY_FIVE_MINUTES => $event->everyFiveMinutes(),
            ScheduleFrequency::EVERY_FIFTEEN_MINUTES => $event->everyFifteenMinutes(),
            ScheduleFrequency::EVERY_THIRTY_MINUTES => $event->everyThirtyMinutes(),
            ScheduleFrequency::HOURLY => $event->hourly(),
            ScheduleFrequency::DAILY => $event->daily(),
            ScheduleFrequency::WEEKLY => $event->weekly(),
        };
    }

    private function scheduleCollector(
        Schedule $schedule,
        string $sourceKey,
        string $collector,
        ScheduleFrequency $scheduleFrequency,
    ): void {
        $this->scheduleAt($schedule->command(CollectCommand::SIGNATURE, [$sourceKey, $collector]), $scheduleFrequency);
    }

    /**
     * @param array<mixed> $taskSchedule
     */
    private function scheduleSiteEndpoints(Schedule $schedule, array $taskSchedule): void
    {
        if (!array_key_exists('site_endpoints', $taskSchedule)) {
            return;
        }

        $scheduleFrequency = Assert::isInstanceOf($taskSchedule['site_endpoints'], ScheduleFrequency::class);

        $this->scheduleAt($schedule->command(CheckSiteEndpointsCommand::SIGNATURE), $scheduleFrequency);
    }

    private function scheduleSource(Schedule $schedule, SourceConfig $sourceConfig): void
    {
        foreach ($sourceConfig->schedule() as $collector => $scheduleFrequency) {
            $this->scheduleCollector($schedule, $sourceConfig->key, $collector, $scheduleFrequency);
        }
    }
}
