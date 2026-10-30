<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

test('alert types of all groups are every alert type once', function (): void {
    $alertTypes = [];

    foreach (AlertTypeGroup::cases() as $alertTypeGroup) {
        array_push($alertTypes, ...$alertTypeGroup->alertTypes());
    }

    expect($alertTypes)
        ->toEqualCanonicalizing(AlertType::cases());
});
