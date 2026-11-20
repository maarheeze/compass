<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Carbon\CarbonImmutable;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\ProcessUtils;
use Maarheeze\Compass\Collections\ScheduledCommandCollection;
use Maarheeze\Compass\Collector\CollectorType;
use Psr\Clock\ClockInterface;

use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;

readonly class ScheduledCommands
{
    private const string COMMAND_PREFIX = 'compass:';

    public function __construct(
        private ClockInterface $clock,
        private Kernel $kernel,
        private Schedule $schedule,
    ) {
    }

    public function all(): ScheduledCommandCollection
    {
        $scheduledCommands = new ScheduledCommandCollection();

        foreach ($this->schedule->events() as $event) {
            $scheduledCommand = $this->scheduledCommand($event);

            if ($scheduledCommand === null) {
                continue;
            }

            $scheduledCommands->push($scheduledCommand);
        }

        return $scheduledCommands;
    }

    public function collectorNextRunAt(string $source, CollectorType $collectorType): ?CarbonImmutable
    {
        $collectCommand = sprintf(
            '%s %s %s',
            CollectCommand::SIGNATURE,
            ProcessUtils::escapeArgument($source),
            ProcessUtils::escapeArgument($collectorType->value),
        );

        return $this->all()->first(static function (ScheduledCommand $scheduledCommand) use ($collectCommand): bool {
            return $scheduledCommand->command === $collectCommand;
        })?->nextRunAt;
    }

    public function run(string $mutexName): void
    {
        $this->kernel->call($this->all()->withMutexName($mutexName)->command);
    }

    private function command(Event $event): ?string
    {
        $artisanPrefix = sprintf('%s %s ', Application::phpBinary(), Application::artisanBinary());

        if ($event->command === null || !str_starts_with($event->command, $artisanPrefix)) {
            return null;
        }

        $command = substr($event->command, strlen($artisanPrefix));

        if (!str_starts_with($command, self::COMMAND_PREFIX)) {
            return null;
        }

        return $command;
    }

    private function scheduledCommand(Event $event): ?ScheduledCommand
    {
        $command = $this->command($event);

        if ($command === null) {
            return null;
        }

        return new ScheduledCommand(
            $command,
            $event->mutexName(),
            CarbonImmutable::instance($event->nextRunDate($this->clock->now())),
        );
    }
}
