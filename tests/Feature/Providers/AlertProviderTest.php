<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertTypes;

test('every registered alert type has a translation', function (): void {
    $alertTypes = $this->app->make(AlertTypes::class);
    $translator = $this->app->make(Translator::class);

    $identifiersWithoutTranslation = array_filter(
        $alertTypes->identifiers(),
        static function (string $identifier) use ($alertTypes, $translator): bool {
            return !$translator->has($alertTypes->alertType($identifier)->translationKey());
        },
    );

    expect($identifiersWithoutTranslation)
        ->toBeEmpty();
});
