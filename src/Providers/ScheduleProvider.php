<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\CollectCommand;

class ScheduleProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule, Application $app): void {
            foreach ($app->make(SourceConfigs::class)->all() as $sourceConfig) {
                $this->scheduleSource($schedule, $sourceConfig);
            }

            $schedule->command(CheckSiteEndpointsCommand::SIGNATURE)->everyMinute();
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
        string $source,
        string $collector,
        ScheduleFrequency $scheduleFrequency,
    ): void {
        $this->scheduleAt($schedule->command(CollectCommand::SIGNATURE, [$source, $collector]), $scheduleFrequency);
    }

    private function scheduleSource(Schedule $schedule, SourceConfig $sourceConfig): void
    {
        foreach ($sourceConfig->schedule() as $collector => $scheduleFrequency) {
            $this->scheduleCollector($schedule, $sourceConfig->key, $collector, $scheduleFrequency);
        }
    }
}
