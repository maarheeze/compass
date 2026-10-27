<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\View\Components\EnumLabel;
use Maarheeze\Compass\View\EnumLabels;

test('label key is the label key of the enum value', function (): void {
    $runType = fake()->randomElement(RunType::cases());

    $enumLabel = $this->app->make(EnumLabel::class, ['value' => $runType]);

    expect($enumLabel->labelKey)
        ->toBe($this->app->make(EnumLabels::class)->labelKey($runType));
});
