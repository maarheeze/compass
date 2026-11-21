<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Carbon\CarbonImmutable;
use Illuminate\Support\ProcessUtils;
use Maarheeze\Compass\Console\CollectCommand;
use Maarheeze\Compass\Scheduling\ScheduledCommands;

use function sprintf;

readonly class CollectorSchedule
{
    public function __construct(
        private ScheduledCommands $scheduledCommands,
    ) {
    }

    public function nextRunAt(string $source, CollectorType $collectorType): ?CarbonImmutable
    {
        $collectCommand = sprintf(
            '%s %s %s',
            CollectCommand::SIGNATURE,
            ProcessUtils::escapeArgument($source),
            ProcessUtils::escapeArgument($collectorType->value),
        );

        return $this->scheduledCommands
            ->all()
            ->withCommand($collectCommand)
            ?->nextRunAt;
    }
}
