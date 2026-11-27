<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyser\Processes\SchedulerHealth;

test('is judged only when the scheduler of a site with tasks was judged', function (
    SchedulerHealth $schedulerHealth,
    bool $isJudged,
): void {
    $judged = $schedulerHealth->isJudged();

    expect($judged)
        ->toBe($isJudged);
})->with([
    'not running' => [SchedulerHealth::NOT_RUNNING, true],
    'running' => [SchedulerHealth::RUNNING, true],
    'no tasks' => [SchedulerHealth::NO_TASKS, false],
    'unknown' => [SchedulerHealth::UNKNOWN, false],
]);
