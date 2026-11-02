<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Console\SyncGitHubCommand;
use Webmozart\Assert\Assert;

class ScheduleProvider extends ServiceProvider
{
    private Repository $config;

    public function boot(Repository $config): void
    {
        $this->config = $config;

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $this->scheduleCommand($schedule, SyncForgeCommand::class, $this->scheduleFrequency('compass.schedule.forge'));
            $this->scheduleCommand($schedule, SyncGitHubCommand::class, $this->scheduleFrequency('compass.schedule.github'));
            $this->scheduleCommand($schedule, CheckSiteEndpointsCommand::class, $this->scheduleFrequency('compass.schedule.site_endpoints'));
        });
    }

    private function scheduleCommand(Schedule $schedule, string $command, ScheduleFrequency $scheduleFrequency): void
    {
        if ($scheduleFrequency === ScheduleFrequency::NEVER) {
            return;
        }

        $event = $schedule->command($command);

        match ($scheduleFrequency) {
            ScheduleFrequency::EVERY_FIVE_MINUTES => $event->everyFiveMinutes(),
            ScheduleFrequency::EVERY_FIFTEEN_MINUTES => $event->everyFifteenMinutes(),
            ScheduleFrequency::EVERY_THIRTY_MINUTES => $event->everyThirtyMinutes(),
            ScheduleFrequency::HOURLY => $event->hourly(),
            ScheduleFrequency::DAILY => $event->daily(),
            ScheduleFrequency::WEEKLY => $event->weekly(),
        };
    }

    private function scheduleFrequency(string $scheduleFrequencyKey): ScheduleFrequency
    {
        $scheduleFrequency = $this->config->get($scheduleFrequencyKey);
        Assert::isInstanceOf($scheduleFrequency, ScheduleFrequency::class);

        return $scheduleFrequency;
    }
}
