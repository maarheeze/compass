<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\View\Components\EnumLabel;

test('label is the translated label of the enum value', function (): void {
    $enumLabel = $this->app->make(EnumLabel::class, ['value' => RunStatus::FAILED]);

    expect($enumLabel->label)
        ->toBe('Failed');
});
