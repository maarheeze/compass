<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Webmozart\Assert\Assert;

use function array_key_exists;

readonly class SourceConfig
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public string $key,
        private array $settings,
    ) {
    }

    public function driver(): string
    {
        return $this->setting('driver');
    }

    public function integerSetting(string $settingName): int
    {
        Assert::keyExists($this->settings, $settingName);

        return (int) Assert::integerish($this->settings[$settingName]);
    }

    /**
     * @return array<string, ScheduleFrequency>
     */
    public function schedule(): array
    {
        if (!array_key_exists('schedule', $this->settings)) {
            return [];
        }

        $schedule = [];

        foreach (Assert::isMap($this->settings['schedule']) as $collector => $scheduleFrequency) {
            $schedule[$collector] = Assert::isInstanceOf($scheduleFrequency, ScheduleFrequency::class);
        }

        return $schedule;
    }

    public function setting(string $settingName): string
    {
        Assert::keyExists($this->settings, $settingName);

        return Assert::stringNotEmpty($this->settings[$settingName]);
    }
}
