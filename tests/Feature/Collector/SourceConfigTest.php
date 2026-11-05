<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Collector\SourceConfig;

test('driver is the driver the source names', function (): void {
    $driverName = fake()->slug();
    $sourceConfig = new SourceConfig(fake()->slug(), ['driver' => $driverName]);

    $driver = $sourceConfig->driver();

    expect($driver)
        ->toBe($driverName);
});

test('setting is the setting of the source under its name', function (): void {
    $settingName = fake()->word();
    $settingValue = fake()->sha1();
    $sourceConfig = new SourceConfig(fake()->slug(), [$settingName => $settingValue]);

    $setting = $sourceConfig->setting($settingName);

    expect($setting)
        ->toBe($settingValue);
});

test('setting refuses a setting the source does not have', function (): void {
    $sourceConfig = new SourceConfig(fake()->slug(), []);

    $this->expectException(InvalidArgumentException::class);

    $sourceConfig->setting(fake()->word());
});

test('setting refuses a setting that is unset, empty or not a string', function (mixed $settingValue): void {
    $settingName = fake()->word();
    $sourceConfig = new SourceConfig(fake()->slug(), [$settingName => $settingValue]);

    $this->expectException(InvalidArgumentException::class);

    $sourceConfig->setting($settingName);
})->with([
    'unset' => [null],
    'empty' => [''],
    'not a string' => [
        function (): int {
            return fake()->randomNumber();
        },
    ],
]);

test('schedule is the frequency of each collector the source schedules', function (): void {
    $collector = fake()->randomElement(CollectorType::cases())->value;
    $scheduleFrequency = fake()->randomElement(ScheduleFrequency::cases());
    $sourceConfig = new SourceConfig(fake()->slug(), ['schedule' => [$collector => $scheduleFrequency]]);

    $schedule = $sourceConfig->schedule();

    expect($schedule)
        ->toBe([$collector => $scheduleFrequency]);
});

test('schedule is empty for a source without a schedule', function (): void {
    $sourceConfig = new SourceConfig(fake()->slug(), ['driver' => fake()->slug()]);

    $schedule = $sourceConfig->schedule();

    expect($schedule)
        ->toBeEmpty();
});

test('schedule refuses a frequency that is not a schedule frequency', function (): void {
    $sourceConfig = new SourceConfig(fake()->slug(), [
        'schedule' => [
            fake()->randomElement(CollectorType::cases())->value => fake()->word(),
        ],
    ]);

    $this->expectException(InvalidArgumentException::class);

    $sourceConfig->schedule();
});
