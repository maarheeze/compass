<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Console\ScheduledCommand;

use function array_values;

/**
 * @extends Collection<int, ScheduledCommand>
 */
class ScheduledCommandCollection extends Collection
{
    /**
     * @return list<string>
     */
    public function mutexNames(): array
    {
        return array_values($this
            ->toBase()
            ->map(static function (ScheduledCommand $scheduledCommand): string {
                return $scheduledCommand->mutexName;
            })
            ->all());
    }

    public function withMutexName(string $mutexName): ScheduledCommand
    {
        return $this->firstOrFail(static function (ScheduledCommand $scheduledCommand) use ($mutexName): bool {
            return $scheduledCommand->mutexName === $mutexName;
        });
    }
}
