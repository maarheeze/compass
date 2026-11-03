<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Console\CollectCommand;
use Webmozart\Assert\Assert;

use function array_key_exists;

class ScheduleProvider extends ServiceProvider
{
    public function boot(Repository $config): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) use ($config): void {
            foreach ($config->array('compass.sources') as $sourceKey => $sourceConfig) {
                $this->scheduleSource($schedule, Assert::stringNotEmpty($sourceKey), Assert::isMap($sourceConfig));
            }
        });
    }

    private function scheduleCollector(
        Schedule $schedule,
        string $sourceKey,
        string $collector,
        ScheduleFrequency $scheduleFrequency,
    ): void {
        $event = $schedule->command(CollectCommand::SIGNATURE, [$sourceKey, $collector]);

        match ($scheduleFrequency) {
            ScheduleFrequency::EVERY_FIVE_MINUTES => $event->everyFiveMinutes(),
            ScheduleFrequency::EVERY_FIFTEEN_MINUTES => $event->everyFifteenMinutes(),
            ScheduleFrequency::EVERY_THIRTY_MINUTES => $event->everyThirtyMinutes(),
            ScheduleFrequency::HOURLY => $event->hourly(),
            ScheduleFrequency::DAILY => $event->daily(),
            ScheduleFrequency::WEEKLY => $event->weekly(),
        };
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function scheduleSource(Schedule $schedule, string $sourceKey, array $sourceConfig): void
    {
        if (!array_key_exists('schedule', $sourceConfig)) {
            return;
        }

        foreach (Assert::isMap($sourceConfig['schedule']) as $collector => $scheduleFrequency) {
            Assert::isInstanceOf($scheduleFrequency, ScheduleFrequency::class);

            $this->scheduleCollector($schedule, $sourceKey, $collector, $scheduleFrequency);
        }
    }
}
