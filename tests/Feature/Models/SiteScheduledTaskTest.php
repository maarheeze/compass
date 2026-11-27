<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\SiteScheduledTask;

test('displayed command is the artisan command without its prefix', function (): void {
    $command = fake()->slug();
    $siteScheduledTask = SiteScheduledTask::factory()->make(['command' => sprintf('php artisan %s', $command)]);

    $displayedCommand = $siteScheduledTask->displayedCommand();

    expect($displayedCommand)
        ->toBe($command);
});

test('displayed command is the command itself when it is not an artisan command', function (): void {
    $command = fake()->sentence();
    $siteScheduledTask = SiteScheduledTask::factory()->make(['command' => $command]);

    $displayedCommand = $siteScheduledTask->displayedCommand();

    expect($displayedCommand)
        ->toBe($command);
});
